<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentSubmission extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'anonymous_session_id',
        'answers',
        'completed_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(RecommendationResult::class);
    }
}