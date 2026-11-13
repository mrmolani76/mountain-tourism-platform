<?php

namespace Modules\Pos\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Pos\App\Http\Requests\AddOrderItemRequest;
use Modules\Pos\App\Http\Requests\OpenOrderRequest;
use Modules\Pos\App\Http\Requests\SettleOrderRequest;
use Modules\Pos\App\Models\Order;
use Modules\Pos\App\Models\OrderItem;
use Modules\Pos\App\Services\OrderService;
use Modules\Pos\App\Support\PosAuthz;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $query = Order::on(config('tenancy.connection_name'))->orderByDesc('id');
        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', (int) $request->query('outlet_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $rows = $query->limit(50)->get()->map(fn (Order $o) => $o->toApiArray())->all();

        return response()->json(['results' => $rows], 200);
    }

    public function store(OpenOrderRequest $request): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $order = $this->orders->open($request->validated());

        return response()->json($order->toApiArray(), 201);
    }

    public function show(int $order): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $model = Order::on(config('tenancy.connection_name'))->find($order);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        return response()->json([
            'order' => $model->toApiArray(),
            'items' => OrderItem::on(config('tenancy.connection_name'))
                ->where('order_id', $model->getKey())
                ->orderBy('id')
                ->get()
                ->map(fn (OrderItem $i) => $i->toApiArray())
                ->all(),
        ], 200);
    }

    public function addItem(AddOrderItemRequest $request, int $order): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $model = Order::on(config('tenancy.connection_name'))->find($order);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $item = $this->orders->addItem($model, $request->validated());

        return response()->json($item->toApiArray(), 201);
    }

    public function settle(SettleOrderRequest $request, int $order): JsonResponse
    {
        if ($denied = PosAuthz::denyUnlessCashierPlus()) {
            return $denied;
        }

        $model = Order::on(config('tenancy.connection_name'))->find($order);
        if ($model === null) {
            return Envelope::errorJson('not_found', 'Not found.', [], 404);
        }

        $result = $this->orders->settle($model, $request->validated('payment_method'));

        return response()->json([
            'order' => $result['order']->toApiArray(),
            'kitchen_ticket' => $result['kitchen_ticket'],
            'receipt' => $result['receipt'],
        ], 200);
    }
}
