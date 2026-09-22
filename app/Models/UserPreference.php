<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'language',
        'theme',
        'reminder_time',
        'email_notifications',
        'push_notifications',
        'offline_photo_quality',
        'last_content_sync_at',
    ];

    protected function casts(): array
    {
        return [
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'last_content_sync_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}