<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CommodityMethod extends Pivot
{
    use HasUuids;

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'commodity_methods';

    protected $fillable = [
        'commodity_id',
        'cultivation_method_id',
        'suitability_score',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'suitability_score' => 'integer',
        ];
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    public function cultivationMethod(): BelongsTo
    {
        return $this->belongsTo(CultivationMethod::class);
    }
}