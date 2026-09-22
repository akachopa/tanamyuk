<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_id',
        'provider',
        'provider_reference',
        'merchant_reference',
        'payment_channel_id',
        'pay_code',
        'qr_url',
        'checkout_url',
        'amount',
        'fee',
        'status',
        'provider_expired_at',
        'paid_at',
        'provider_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'provider_expired_at' => 'datetime',
            'paid_at' => 'datetime',
            'provider_response' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentChannel(): BelongsTo
    {
        return $this->belongsTo(PaymentChannel::class);
    }
}