<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommodityVariety extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'commodity_id',
        'name',
        'description',
        'harvest_min_days',
        'harvest_max_days',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'harvest_min_days' => 'integer',
            'harvest_max_days' => 'integer',
        ];
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(CultivationTemplate::class, 'variety_id');
    }
}