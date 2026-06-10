<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'total_points',
        'photo',
        'verification_code',
        'verification_code_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(Task::class, Subject::class);
    }

    public function collaboratingTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function taskActivities(): HasMany
    {
        return $this->hasMany(TaskActivity::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'user_id');
    }

    public function collaboratingNotes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_user')
            ->withPivot('role')
            ->withTimestamps();
    }
}