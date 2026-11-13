<?php

namespace Modules\Pos\App\Services;

use App\Support\Http\Envelope;
use Illuminate\Support\Facades\DB;
use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Models\Folio;
use Modules\Folio\App\Models\Payment;
use Modules\Folio\App\Services\FolioService;
use Modules\Hotel\App\Models\Stay;
use Modules\Pos\App\Models\Order;
use Modules\Pos\App\Models\OrderItem;
use Modules\Pos\App\Models\Product;

/**
 * POS order lifecycle: open → lines → settle (pay now or charge to room).
 * Board-included lines stay on the order but do not create folio charges.
 */
class OrderService
{
    public function __construct(
        private readonly ShiftService $shifts,
        private readonly BoardCheckService $board,
        private readonly FolioService $folios,
    ) {
    }

    public function open(array $data): Order
    {
        $outletId = (int) $data['outlet_id'];
        $shift = $this->shifts->requireOpenShift($outletId);

        $stayId = isset($data['stay_id']) ? (int) $data['stay_id'] : null;
        if ($stayId !== null) {
            $stay = Stay::on(config('tenancy.connection_name'))->find($stayId);
            if ($stay === null || ! $stay->isInHouse()) {
                Envelope::abort('stay_not_in_house', 'Stay must be in-house for guest context.', [], 422);
            }
        }

        return Order::on(config('tenancy.connection_name'))->create([
            'outlet_id' => $outletId,
            'shift_id' => $shift->getKey(),
            'stay_id' => $stayId,
            'folio_id' => $stayId === null ? null : Stay::on(config('tenancy.connection_name'))
                ->find($stayId)?->getAttribute('folio_id'),
            'table_label' => $data['table_label'] ?? null,
            'status' => Order::STATUS_OPEN,
            'opened_by' => $this->actorId(),
            'opened_at' => now(),
        ]);
    }

    public function addItem(Order $order, array $data): OrderItem
    {
        $this->assertOpen($order);

        $product = Product::on(config('tenancy.connection_name'))->find((int) $data['product_id']);
        if ($product === null || ! $product->getAttribute('is_active')) {
            Envelope::abort('product_unavailable', 'Product not found or inactive.', [], 422);
        }

        $qty = (int) $data['quantity'];
        if ($qty < 1) {
            Envelope::abort('invalid_quantity', 'Quantity must be at least 1.', [], 400);
        }

        $unit = (int) $product->getAttribute('price_cents');
        $taxBp = (int) $product->getAttribute('tax_rate_bp');
        $lineTotal = $unit * $qty;
        $taxCents = (int) round($lineTotal * $taxBp / 10000);
        $boardIncluded = (bool) ($data['is_board_included'] ?? false);

        if ($boardIncluded) {
            $meal = (string) ($data['meal'] ?? '');
            $stayId = $order->getAttribute('stay_id');
            if ($stayId === null) {
                Envelope::abort('board_needs_stay', 'Board-included lines require a stay on the order.', [], 422);
            }
            $check = $this->board->check((int) $stayId, $meal, $data['service_date'] ?? null);
            if (! $check['can_claim']) {
                Envelope::abort(
                    'board_not_claimable',
                    'Meal is not included or already claimed.',
                    $check,
                    422
                );
            }
        }

        $item = OrderItem::on(config('tenancy.connection_name'))->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
            'description' => $product->getAttribute('name'),
            'quantity' => $qty,
            'unit_price_cents' => $unit,
            'tax_rate_bp' => $taxBp,
            'tax_cents' => $boardIncluded ? 0 : $taxCents,
            'line_total_cents' => $boardIncluded ? 0 : $lineTotal,
            'note' => $data['note'] ?? null,
            'is_board_included' => $boardIncluded,
        ]);

        if ($boardIncluded) {
            $this->board->claim(
                (int) $order->getAttribute('stay_id'),
                (string) $data['meal'],
                $data['service_date'] ?? null,
                (int) $order->getKey(),
            );
        }

        $this->recomputeTotals($order);

        return $item;
    }

    /**
     * @return array{order: Order, kitchen_ticket: array<string, mixed>, receipt: array<string, mixed>}
     */
    public function settle(Order $order, string $method): array
    {
        $this->assertOpen($order);

        if (! in_array($method, Order::METHODS, true)) {
            Envelope::abort('invalid_method', 'payment_method must be cash, card, or room.', [], 400);
        }

        $items = OrderItem::on(config('tenancy.connection_name'))
            ->where('order_id', $order->getKey())
            ->get();

        if ($items->isEmpty()) {
            Envelope::abort('order_empty', 'Add at least one line before settle.', [], 422);
        }

        return DB::connection(config('tenancy.connection_name'))->transaction(function () use ($order, $method, $items) {
            $payableItems = $items->where('is_board_included', false);
            $payableSubtotal = (int) $payableItems->sum('line_total_cents');
            $payableTax = (int) $payableItems->sum('tax_cents');
            $payableTotal = $payableSubtotal + $payableTax;

            $folio = $this->resolveFolio($order, $method);
            $chargeId = null;

            if ($payableTotal > 0) {
                $charge = $this->folios->postCharge($folio, [
                    'outlet_id' => (int) $order->getAttribute('outlet_id'),
                    'description' => 'POS order #'.$order->getKey(),
                    'amount_cents' => $payableTotal,
                    'tax_cents' => $payableTax,
                    'source_type' => Charge::SOURCE_ORDER,
                    'source_id' => (string) $order->getKey(),
                    'stay_id' => $order->getAttribute('stay_id'),
                ]);
                $chargeId = (int) $charge->getKey();

                if ($method === Order::METHOD_CASH || $method === Order::METHOD_CARD) {
                    $this->folios->recordPayment($folio, [
                        'method' => $method === Order::METHOD_CASH
                            ? Payment::METHOD_CASH
                            : Payment::METHOD_CARD,
                        'amount_cents' => $payableTotal,
                        'reference' => 'pos-order-'.$order->getKey(),
                    ]);

                    if ($method === Order::METHOD_CASH && $order->getAttribute('shift_id')) {
                        $shift = $order->shift;
                        if ($shift !== null && $shift->isOpen()) {
                            $this->shifts->addExpectedCash($shift, $payableTotal);
                        }
                    }

                    // Walk-in one-off folio can close immediately when settled.
                    if ($order->getAttribute('stay_id') === null) {
                        $this->folios->close($folio->fresh());
                    }
                }
            }

            $order->setAttribute('folio_id', (int) $folio->getKey());
            $order->setAttribute('charge_id', $chargeId);
            $order->setAttribute('payment_method', $method);
            $order->setAttribute(
                'status',
                $method === Order::METHOD_ROOM ? Order::STATUS_BILLED : Order::STATUS_PAID
            );
            $order->setAttribute('closed_at', now());
            $order->save();

            $fresh = $order->fresh();
            $payloadItems = $items->map(fn (OrderItem $i) => $i->toApiArray())->all();

            return [
                'order' => $fresh,
                'kitchen_ticket' => [
                    'order_id' => (int) $fresh->getKey(),
                    'outlet_id' => (int) $fresh->getAttribute('outlet_id'),
                    'table_label' => $fresh->getAttribute('table_label'),
                    'items' => $payloadItems,
                    'printed_at' => now()->toIso8601String(),
                ],
                'receipt' => [
                    'order_id' => (int) $fresh->getKey(),
                    'payment_method' => $method,
                    'subtotal_cents' => (int) $fresh->getAttribute('subtotal_cents'),
                    'tax_cents' => (int) $fresh->getAttribute('tax_cents'),
                    'total_cents' => (int) $fresh->getAttribute('total_cents'),
                    'folio_id' => (int) $folio->getKey(),
                    'charge_id' => $chargeId,
                    'items' => $payloadItems,
                    'printed_at' => now()->toIso8601String(),
                ],
            ];
        });
    }

    private function resolveFolio(Order $order, string $method): Folio
    {
        if ($method === Order::METHOD_ROOM) {
            $stayId = $order->getAttribute('stay_id');
            if ($stayId === null) {
                Envelope::abort('room_needs_stay', 'Charge to room requires stay_id on the order.', [], 422);
            }
            $stay = Stay::on(config('tenancy.connection_name'))->find((int) $stayId);
            $folioId = $stay?->getAttribute('folio_id');
            $folio = $folioId === null
                ? null
                : Folio::on(config('tenancy.connection_name'))->find((int) $folioId);
            if ($folio === null || ! $folio->isOpen()) {
                Envelope::abort('folio_missing', 'Stay has no open folio for charge to room.', [], 422);
            }

            return $folio;
        }

        $existingId = $order->getAttribute('folio_id');
        if ($existingId !== null) {
            $existing = Folio::on(config('tenancy.connection_name'))->find((int) $existingId);
            if ($existing !== null && $existing->isOpen()) {
                return $existing;
            }
        }

        // Walk-in / pay-now: one-off folio so Module C remains money source of truth.
        return $this->folios->open('pos-order-'.$order->getKey());
    }

    private function recomputeTotals(Order $order): void
    {
        $items = OrderItem::on(config('tenancy.connection_name'))
            ->where('order_id', $order->getKey())
            ->get();

        $payable = $items->where('is_board_included', false);
        $subtotal = (int) $payable->sum('line_total_cents');
        $tax = (int) $payable->sum('tax_cents');

        $order->setAttribute('subtotal_cents', $subtotal);
        $order->setAttribute('tax_cents', $tax);
        $order->setAttribute('total_cents', $subtotal + $tax);
        $order->save();
    }

    private function assertOpen(Order $order): void
    {
        if (! $order->isOpen()) {
            Envelope::abort('order_closed', 'Order is not open for changes.', [
                'status' => $order->getAttribute('status'),
            ], 422);
        }
    }

    private function actorId(): ?int
    {
        if (app()->bound('staff')) {
            return (int) app('staff')->getKey();
        }

        return null;
    }
}
