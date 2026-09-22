<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CultivationTemplate extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'commodity_id',
        'variety_id',
        'cultivation_method_id',
        'name',
        'version',
        'duration_days',
        'difficulty_level',
        'description',
        'requirements',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'requirements' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    public function variety(): BelongsTo
    {
        return $this->belongsTo(CommodityVariety::class, 'variety_id');
    }

    public function cultivationMethod(): BelongsTo
    {
        return $this->belongsTo(CultivationMethod::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(TemplateStage::class, 'template_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TemplateTask::class, 'template_id');
    }

    public function plantingCycles(): HasMany
    {
        return $this->hasMany(PlantingCycle::class, 'template_id');
    }
}