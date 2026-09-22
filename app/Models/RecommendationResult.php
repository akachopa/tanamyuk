<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationResult extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'assessment_submission_id',
        'commodity_id',
        'score',
        'reasons',
        'rank',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'reasons' => 'array',
            'rank' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function assessmentSubmission(): BelongsTo
    {
        return $this->belongsTo(AssessmentSubmission::class);
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }
}