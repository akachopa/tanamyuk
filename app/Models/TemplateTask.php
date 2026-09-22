<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateTask extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'template_id',
        'stage_id',
        'task_type',
        'title',
        'instruction',
        'first_day_offset',
        'repeat_interval_days',
        'repeat_until_day_offset',
        'estimated_minutes',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'first_day_offset' => 'integer',
            'repeat_interval_days' => 'integer',
            'repeat_until_day_offset' => 'integer',
            'estimated_minutes' => 'integer',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CultivationTemplate::class, 'template_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(TemplateStage::class, 'stage_id');
    }
}