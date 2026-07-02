<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Publication extends Model
{
    /** Publication type, mapped to its human label. */
    public const TYPES = [
        'working_paper' => 'Working paper',
        'research_paper' => 'Research paper',
        'policy_brief' => 'Policy brief',
        'strategy_paper' => 'Strategy paper',
        'report' => 'Report',
        'critical_column' => 'Critical column',
        'essay' => 'Essay',
    ];

    /** Editorial workflow stages, in order, mapped to their human label. */
    public const STATUSES = [
        'draft' => 'Draft',
        'internal_review' => 'Internal review',
        'peer_review' => 'Peer review',
        'revision' => 'Revision',
        'copy_edit' => 'Copy-editing',
        'approved' => 'Approved',
        'published' => 'Published',
        'archived' => 'Archived',
    ];

    protected $fillable = [
        'research_project_id', 'topic_id', 'region_id', 'title', 'slug', 'type', 'status', 'language',
        'abstract', 'body', 'doi', 'citation', 'downloads', 'current_version_id',
        'published_at', 'path', 'original_name', 'mime', 'size', 'published_in',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'downloads' => 'integer',
        'published_in' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    protected static function booted(): void
    {
        static::saving(function (Publication $publication): void {
            if (blank($publication->slug)) {
                $publication->slug = static::uniqueSlug($publication->title);
            }

            if ($publication->status === 'published' && blank($publication->published_at)) {
                $publication->published_at = now();
            }
        });

        static::deleting(fn (Publication $publication) => $publication->comments()->delete());
    }

    protected static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'publication';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ResearchProject::class, 'research_project_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /** Public URL to the attached document, if any. */
    public function url(): ?string
    {
        return $this->path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->path) : null;
    }

    /**
     * Authors as "Lastname, Firstname", comma-separated. Falls back to the full
     * name when it cannot be split.
     */
    public function authorLine(): string
    {
        return $this->authors->map(function (User $author): string {
            $parts = preg_split('/\s+/', trim($author->name)) ?: [];
            if (count($parts) < 2) {
                return $author->name;
            }
            $last = array_pop($parts);

            return $last . ', ' . implode(' ', $parts);
        })->implode('; ') ?: '—';
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'publication_author')
            ->withPivot('role', 'order')
            ->withTimestamps()
            ->orderBy('publication_author.order');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PublicationVersion::class)->orderByDesc('version_no');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
