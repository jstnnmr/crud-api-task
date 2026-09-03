<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'color',
    ];

    public function getPreviewAttribute(): string
{
    return $this->content
        ? str(strip_tags($this->content))->limit(120)->toString()
        : 'Empty note';
}

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'note_user')
            ->withPivot('role')
            ->withTimestamps();
    }
}
