<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commodity extends Model
{
    use HasUuids;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'scientific_name',
        'short_description',
        'cover_image_path',
        'difficulty_level',
        'min_space_m2',
        'sunlight_min_hours',
        'sunlight_max_hours',
        'water_need_level',
        'maintenance_minutes_per_day',
        'harvest_min_days',
        'harvest_max_days',
        'repeat_harvest',
        'default_harvest_unit',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'min_space_m2' => 'decimal:2',
            'sunlight_min_hours' => 'integer',
            'sunlight_max_hours' => 'integer',
            'maintenance_minutes_per_day' => 'integer',
            'harvest_min_days' => 'integer',
            'harvest_max_days' => 'integer',
            'repeat_harvest' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CommodityCategory::class, 'category_id');
    }

    public function varieties(): HasMany
    {
        return $this->hasMany(CommodityVariety::class);
    }

    public function cultivationMethods(): BelongsToMany
    {
        return $this->belongsToMany(CultivationMethod::class, 'commodity_methods')
            ->withPivot(['id', 'suitability_score', 'notes'])
            ->using(CommodityMethod::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(CultivationTemplate::class);
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_commodities')
            ->withPivot(['id'])
            ->using(ArticleCommodity::class);
    }

    public function plantingCycles(): HasMany
    {
        return $this->hasMany(PlantingCycle::class);
    }
}