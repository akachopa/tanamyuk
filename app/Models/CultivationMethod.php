<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CultivationMethod extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
    ];

    public function commodities(): BelongsToMany
    {
        return $this->belongsToMany(Commodity::class, 'commodity_methods')
            ->withPivot(['id', 'suitability_score', 'notes'])
            ->using(CommodityMethod::class);
    }

    public function gardens(): BelongsToMany
    {
        return $this->belongsToMany(Garden::class, 'garden_methods')
            ->withPivot(['id'])
            ->using(GardenMethod::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(CultivationTemplate::class);
    }
}