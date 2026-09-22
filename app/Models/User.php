<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar_path',
        'timezone',
        'experience_level',
        'daily_available_minutes',
        'primary_goal',
        'status',
        'last_login_at',
        'is_admin',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'daily_available_minutes' => 'integer',
            'last_login_at' => 'datetime',
            'is_admin' => 'boolean',
        ];
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function gardens(): HasMany
    {
        return $this->hasMany(Garden::class);
    }

    public function plantingCycles(): HasMany
    {
        return $this->hasMany(PlantingCycle::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function taskEvents(): HasMany
    {
        return $this->hasMany(TaskEvent::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function plantIssues(): HasMany
    {
        return $this->hasMany(PlantIssue::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class);
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function syncMutations(): HasMany
    {
        return $this->hasMany(SyncMutation::class);
    }

    public function assessmentSubmissions(): HasMany
    {
        return $this->hasMany(AssessmentSubmission::class);
    }

    public function productEvents(): HasMany
    {
        return $this->hasMany(ProductEvent::class);
    }
}
