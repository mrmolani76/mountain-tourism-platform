<?php

namespace Modules\Transport\App\Services;

use App\Support\Http\Envelope;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Models\Folio;
use Modules\Folio\App\Models\Payment;
use Modules\Folio\App\Services\FolioService;
use Modules\Hotel\App\Models\Stay;
use Modules\Pos\App\Models\Product;
use Modules\Transport\App\Models\IssuedLiftPass;
use Modules\Transport\App\Models\LiftPassProduct;

class LiftPassService
{
    public function __construct(private readonly FolioService $folios)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function sell(array $data): IssuedLiftPass
    {
        $product = LiftPassProduct::on(config('tenancy.connection_name'))
            ->find((int) $data['lift_pass_product_id']);
        if ($product === null || ! $product->getAttribute('is_active')) {
            Envelope::abort('pass_unavailable', 'Lift pass product not found or inactive.', [], 422);
        }

        $posProduct = Product::on(config('tenancy.connection_name'))
            ->find((int) $product->getAttribute('product_id'));
        if ($posProduct === null || ! $posProduct->getAttribute('is_active')) {
            Envelope::abort('product_unavailable', 'Linked POS product is unavailable.', [], 422);
        }

        $validFrom = CarbonImmutable::parse((string) ($data['valid_from'] ?? CarbonImmutable::now()->toDateString()));
        $days = max(1, (int) $product->getAttribute('valid_days'));
        $validTo = $validFrom->addDays($days - 1);

        return DB::connection(config('tenancy.connection_name'))->transaction(function () use ($data, $product, $posProduct, $validFrom, $validTo) {
            $folio = $this->resolveFolio($data);
            $amount = (int) $posProduct->getAttribute('price_cents');
            $taxBp = (int) $posProduct->getAttribute('tax_rate_bp');
            $tax = (int) round($amount * $taxBp / 10000);

            $pass = IssuedLiftPass::on(config('tenancy.connection_name'))->create([
                'lift_pass_product_id' => $product->getKey(),
                'stay_id' => $data['stay_id'] ?? null,
                'guest_name' => $data['guest_name'],
                'valid_from' => $validFrom->toDateString(),
                'valid_to' => $validTo->toDateString(),
                'issued_by' => $this->actorId(),
            ]);

            if ($folio !== null && $amount > 0) {
                $charge = $this->folios->postCharge($folio, [
                    'description' => 'Lift pass: '.$posProduct->getAttribute('name'),
                    'amount_cents' => $amount,
                    'tax_cents' => $tax,
                    'source_type' => Charge::SOURCE_TRANSPORT,
                    'source_id' => (string) $pass->getKey(),
                    'stay_id' => $pass->getAttribute('stay_id'),
                ]);
                $pass->setAttribute('charge_id', $charge->getKey());
                $pass->save();

                if (($data['pay_now'] ?? false) === true) {
                    $method = ($data['payment_method'] ?? 'cash') === 'card'
                        ? Payment::METHOD_CARD
                        : Payment::METHOD_CASH;
                    $this->folios->recordPayment($folio, [
                        'method' => $method,
                        'amount_cents' => $amount + $tax,
                        'reference' => 'lift-pass-'.$pass->getKey(),
                    ]);
                }
            }

            return $pass->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveFolio(array $data): ?Folio
    {
        if (! empty($data['stay_id'])) {
            $stay = Stay::on(config('tenancy.connection_name'))->find((int) $data['stay_id']);
            if ($stay === null || ! $stay->isInHouse()) {
                Envelope::abort('stay_not_in_house', 'Stay must be in-house.', [], 422);
            }
            $folio = Folio::on(config('tenancy.connection_name'))
                ->find((int) $stay->getAttribute('folio_id'));
            if ($folio === null || ! $folio->isOpen()) {
                Envelope::abort('folio_missing', 'Stay has no open folio.', [], 422);
            }

            return $folio;
        }

        if (($data['pay_now'] ?? false) === true) {
            return $this->folios->open('lift-pass-walk-in');
        }

        return null;
    }

    private function actorId(): ?int
    {
        if (app()->bound('staff')) {
            return (int) app('staff')->getKey();
        }

        return null;
    }
}
