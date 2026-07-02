<?php

namespace App\Models;

use App\Models\Concerns\HasCategories;
use App\Models\Concerns\HasKeywords;
use App\Models\Concerns\HasSocialInteractions;
use App\Models\Concerns\HasTasks;
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
    use HasTasks;

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

    /**
     * Keep cross-references undirected: if this idea links to another, make
     * sure the other links back too, and drop reverse links that were removed.
     * Call after the form has synced this idea's own outgoing links.
     */
    public function syncSymmetricCrossReferences(): void
    {
        $outgoing = \Illuminate\Support\Facades\DB::table('idea_cross_references')
            ->where('idea_id', $this->id)
            ->pluck('related_idea_id')
            ->all();

        foreach ($outgoing as $relatedId) {
            $exists = \Illuminate\Support\Facades\DB::table('idea_cross_references')
                ->where('idea_id', $relatedId)
                ->where('related_idea_id', $this->id)
                ->exists();

            if (! $exists) {
                \Illuminate\Support\Facades\DB::table('idea_cross_references')->insert([
                    'idea_id' => $relatedId,
                    'related_idea_id' => $this->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Remove reverse links to ideas no longer among this idea's references.
        \Illuminate\Support\Facades\DB::table('idea_cross_references')
            ->where('related_idea_id', $this->id)
            ->whereNotIn('idea_id', $outgoing ?: [-1])
            ->delete();
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
