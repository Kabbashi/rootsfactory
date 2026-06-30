<?php

namespace App\Models;

use App\Jobs\GenerateAiInsight;
use App\Models\Concerns\HasCategories;
use App\Models\Concerns\HasKeywords;
use App\Models\Concerns\HasSocialInteractions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class ResearchConcept extends Model
{
    use HasCategories;
    use HasKeywords;
    use HasSocialInteractions;

    public const STATUSES = ['draft', 'in_discussion', 'final'];

    public const STATUS_LABELS = [
        'draft' => 'Draft',
        'in_discussion' => 'In discussion',
        'final' => 'Final',
    ];

    /** Publication types, mapped to their human label (also the badge text). */
    public const TYPES = [
        'brief' => 'Policy brief',
        'analysis' => 'Analysis',
        'report' => 'Report',
        'note' => 'Field note',
    ];

    protected $fillable = ['user_id', 'topic_id', 'region_id', 'origin_idea_id', 'title', 'slug', 'type', 'body', 'status', 'published_at', 'pinned'];

    public function originIdea(): BelongsTo
    {
        return $this->belongsTo(Idea::class, 'origin_idea_id');
    }

    /** Concepts grown from a public idea carry the social layer (P7). */
    public function isFromPublicIdea(): bool
    {
        return $this->originIdea && $this->originIdea->isPublic();
    }

    /** Author plus everyone who offered to collaborate. */
    public function contributorNames(): array
    {
        return collect([$this->user?->name])
            ->merge($this->collaborationOffers->map(fn ($o) => $o->user?->name))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected $casts = [
        'pinned' => 'boolean',
        'published_at' => 'datetime',
    ];

    /** Public URLs are slug-based, not numeric ids. */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Concepts the team has marked final (the finished ones). */
    public function scopeFinal(Builder $query): Builder
    {
        return $query->where('status', 'final');
    }

    public function isFinal(): bool
    {
        return $this->status === 'final';
    }

    /** A final concept is locked for everyone except the person who brought it in. */
    public function isLockedFor(?int $userId): bool
    {
        return $this->isFinal() && $this->user_id !== $userId;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    protected static function booted(): void
    {
        // Keep slug and finalised date in step with the status — covers both
        // Filament edits and programmatic changes.
        static::saving(function (ResearchConcept $idea): void {
            if (blank($idea->slug)) {
                $idea->slug = static::uniqueSlug($idea->title);
            }

            if ($idea->status === 'final' && blank($idea->published_at)) {
                $idea->published_at = now();
            }
        });

        // Polymorphic relations have no DB-level cascade — clean them up here.
        static::deleting(function (ResearchConcept $idea): void {
            $idea->comments()->delete();
            $idea->reactions()->delete();
            $idea->collaborationOffers()->delete();
        });

        // When an idea opens for discussion, let the co-thinker kick it off
        // with a summary (opt-out via config('ai.auto_summary')).
        static::updated(function (ResearchConcept $idea): void {
            if (config('ai.auto_summary')
                && filled(config('ai.key'))
                && $idea->wasChanged('status')
                && $idea->status === 'in_discussion') {
                GenerateAiInsight::for($idea, 'summarize');
            }
        });
    }

    /**
     * A URL-safe slug from the title, suffixed if another idea already owns it.
     */
    protected static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'idea';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /** The human label for this publication's type. */
    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? self::TYPES['brief'];
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
