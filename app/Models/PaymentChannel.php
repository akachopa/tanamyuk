<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentChannel extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'provider',
        'provider_code',
        'name',
        'category',
        'fee_flat',
        'fee_percent',
        'min_amount',
        'max_amount',
        'icon_url',
        'is_enabled_by_provider',
        'is_enabled_by_admin',
        'provider_payload',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'fee_flat' => 'decimal:2',
            'fee_percent' => 'decimal:4',
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'is_enabled_by_provider' => 'boolean',
            'is_enabled_by_admin' => 'boolean',
            'provider_payload' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}