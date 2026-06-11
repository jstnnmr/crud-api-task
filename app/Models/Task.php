<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'category_id',
        'title',
        'description',
        'priority',
        'status',
        'points_earned',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'completed_at' => 'datetime',
    ];

    public const POINTS = [
        'low'    => 5,
        'medium' => 10,
        'high'   => 20,
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TaskActivity::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TaskInvitation::class);
    }
}