<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends ApiController
{
    public function index(): JsonResponse
    {
        $orders = Order::query()
            ->where('user_id', $this->userId())
            ->with('plan')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Order $order) => $this->present($order));

        return $this->ok($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan_id' => ['required', 'uuid', 'exists:plans,id'],
            'channel' => ['nullable', 'string', 'max:50'],
        ]);

        $plan = Plan::query()->where('status', 'active')->findOrFail($data['plan_id']);

        if ($plan->is_free || (float) $plan->price <= 0) {
            return $this->fail('Paket gratis tidak perlu dipesan.', 422);
        }

        $order = Order::query()->create([
            'order_number' => $this->orderNumber(),
            'user_id' => $this->userId(),
            'plan_id' => $plan->id,
            'subtotal' => $plan->price,
            'payment_fee' => 0,
            'total' => $plan->price,
            'currency' => $plan->currency ?: 'IDR',
            'status' => 'pending',
            'expires_at' => now()->addDay(),
        ]);

        $order->setRelation('plan', $plan);

        return $this->ok($this->present($order), 'Pesanan dibuat.', 201);
    }

    public function show(string $orderNumber): JsonResponse
    {
        return $this->ok($this->present($this->findOrder($orderNumber)));
    }

    public function checkStatus(string $orderNumber): JsonResponse
    {
        $order = $this->findOrder($orderNumber);

        return $this->ok([
            'order_number' => $order->order_number,
            'status' => $order->status,
            'paid' => $order->status === 'paid',
            'total' => (float) $order->total,
            'currency' => $order->currency,
            'expires_at' => $order->expires_at,
        ], 'Pengecekan pembayaran otomatis belum tersedia.');
    }

    private function findOrder(string $orderNumber): Order
    {
        return Order::query()
            ->with('plan')
            ->where('user_id', $this->userId())
            ->where('order_number', $orderNumber)
            ->firstOrFail();
    }

    private function orderNumber(): string
    {
        do {
            $number = 'TY-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'subtotal' => (float) $order->subtotal,
            'payment_fee' => (float) $order->payment_fee,
            'total' => (float) $order->total,
            'currency' => $order->currency,
            'expires_at' => $order->expires_at,
            'paid_at' => $order->paid_at,
            'plan' => $order->plan ? [
                'id' => $order->plan->id,
                'code' => $order->plan->code,
                'name' => $order->plan->name,
                'price' => (float) $order->plan->price,
            ] : null,
            'payment' => [
                'method' => null,
                'instructions' => 'Pembayaran QRIS dan virtual account belum terhubung.',
            ],
        ];
    }
}
