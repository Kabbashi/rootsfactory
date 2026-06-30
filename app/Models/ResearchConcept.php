<?php

namespace App\Models;

use App\Jobs\GenerateAiInsight;
use App\Models\Concerns\HasCategories;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class ResearchConcept extends Model
{
    use HasCategories;

    public const STATUSES = ['draft', 'in_discussion', 'published'];

    /** Publication types, mapped to their human label (also the badge text). */
    public const TYPES = [
        'brief' => 'Policy brief',
        'analysis' => 'Analysis',
        'report' => 'Report',
        'note' => 'Field note',
    ];

    protected $fillable = ['user_id', 'topic_id', 'region_id', 'title', 'slug', 'type', 'body', 'status', 'published_at', 'pinned'];

    protected $casts = [
        'pinned' => 'boolean',
        'published_at' => 'datetime',
    ];

    /** Public URLs are slug-based, not numeric ids. */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Only ideas the team has chosen to publish are visible to the public. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    protected static function booted(): void
    {
        // Keep slug and publish date in step with the status — covers both
        // Filament edits and programmatic changes.
        static::saving(function (ResearchConcept $idea): void {
            if (blank($idea->slug)) {
                $idea->slug = static::uniqueSlug($idea->title);
            }

            if ($idea->status === 'published' && blank($idea->published_at)) {
                $idea->published_at = now();
            }
        });

        // Polymorphic comments have no DB-level cascade — clean them up here.
        static::deleting(fn (ResearchConcept $idea) => $idea->comments()->delete());

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
