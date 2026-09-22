<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Harvest extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'planting_cycle_id',
        'harvest_date',
        'quantity',
        'unit',
        'grade',
        'usage_type',
        'sale_value',
        'waste_quantity',
        'notes',
        'client_mutation_id',
        'server_version',
    ];

    protected function casts(): array
    {
        return [
            'harvest_date' => 'date',
            'quantity' => 'decimal:2',
            'sale_value' => 'decimal:2',
            'waste_quantity' => 'decimal:2',
            'server_version' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plantingCycle(): BelongsTo
    {
        return $this->belongsTo(PlantingCycle::class);
    }
}