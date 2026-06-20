<?php

namespace App\Models;

use App\Jobs\GenerateAiInsight;
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

        // When an idea opens for discussion, let the co-thinker kick it off
        // with a summary (opt-out via config('ai.auto_summary')).
        static::updated(function (Idea $idea): void {
            if (config('ai.auto_summary')
                && filled(config('ai.key'))
                && $idea->wasChanged('status')
                && $idea->status === 'in_discussion') {
                GenerateAiInsight::for($idea, 'summarize');
            }
        });
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
