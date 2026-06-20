<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Idea extends Model
{
    public const STATUSES = ['draft', 'in_discussion', 'published'];

    protected $fillable = ['user_id', 'topic_id', 'title', 'body', 'status', 'pinned'];

    protected $casts = [
        'pinned' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Polymorphic comments have no DB-level cascade — clean them up here.
        static::deleting(fn (Idea $idea) => $idea->comments()->delete());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
