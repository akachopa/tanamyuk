<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CycleStage extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'planting_cycle_id',
        'source_template_stage_id',
        'name',
        'description',
        'planned_start_date',
        'planned_end_date',
        'started_at',
        'completed_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'planned_start_date' => 'date',
            'planned_end_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function plantingCycle(): BelongsTo
    {
        return $this->belongsTo(PlantingCycle::class);
    }

    public function sourceTemplateStage(): BelongsTo
    {
        return $this->belongsTo(TemplateStage::class, 'source_template_stage_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}