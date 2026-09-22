<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PlanFeature extends Pivot
{
    use HasUuids;

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'plan_features';

    protected $fillable = [
        'plan_id',
        'feature_id',
        'value_boolean',
        'value_number',
        'value_text',
    ];

    protected function casts(): array
    {
        return [
            'value_boolean' => 'boolean',
            'value_number' => 'decimal:2',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }
}