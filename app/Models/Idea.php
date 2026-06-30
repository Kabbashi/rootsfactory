<?php

namespace App\Models;

use App\Models\Concerns\HasCategories;
use App\Models\Concerns\HasKeywords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * An Idea Pool item: a brainstorming note built around a central core
 * statement. Public ideas are visible to the whole network; personal ideas
 * are visible only to their author (not even to the rest of the team).
 */
class Idea extends Model
{
    use HasCategories;
    use HasKeywords;

    public const VISIBILITIES = [
        'personal' => 'Personal — only me',
        'public' => 'Public — the whole network',
    ];

    protected $fillable = ['user_id', 'name', 'core_statement', 'description', 'visibility'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function visibilityLabel(): string
    {
        return self::VISIBILITIES[$this->visibility] ?? $this->visibility;
    }

    /** Public ideas, plus the given user's own personal ideas. */
    public function scopeVisibleTo(Builder $query, ?int $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId): void {
            $q->where('visibility', 'public');
            if ($userId !== null) {
                $q->orWhere('user_id', $userId);
            }
        });
    }

    protected static function booted(): void
    {
        // Polymorphic comments have no DB-level cascade — clean them up.
        static::deleting(fn (Idea $idea) => $idea->comments()->delete());
    }
}
