<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateStage extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'template_id',
        'name',
        'description',
        'start_day_offset',
        'end_day_offset',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'start_day_offset' => 'integer',
            'end_day_offset' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CultivationTemplate::class, 'template_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TemplateTask::class, 'stage_id');
    }
}