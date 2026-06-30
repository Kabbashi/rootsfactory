<?php

namespace App\Models;

use App\Models\Concerns\HasCategories;
use App\Models\Concerns\HasKeywords;
use App\Models\Concerns\HasSocialInteractions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
    use HasSocialInteractions;

    public const VISIBILITIES = [
        'personal' => 'Personal — only me',
        'public' => 'Public — the whole network',
    ];

    protected $fillable = ['user_id', 'name', 'core_statement', 'description', 'image_path', 'attachments', 'visibility'];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /** Other ideas this one links to (mindmap edges). */
    public function crossReferences(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'idea_cross_references',
            'idea_id',
            'related_idea_id',
        )->withTimestamps();
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
        // Polymorphic relations have no DB-level cascade — clean them up.
        static::deleting(function (Idea $idea): void {
            $idea->comments()->delete();
            $idea->reactions()->delete();
            $idea->collaborationOffers()->delete();
            \Illuminate\Support\Facades\Storage::disk('public')->delete($idea->attachments ?? []);
        });
    }
}
