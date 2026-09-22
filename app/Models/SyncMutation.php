<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncMutation extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'device_id',
        'client_mutation_id',
        'entity_type',
        'entity_id',
        'operation',
        'payload_hash',
        'processing_status',
        'processed_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}