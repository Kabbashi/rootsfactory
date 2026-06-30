<?php

namespace App\Models;

use App\Models\Concerns\HasCategories;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class ResearchProject extends Model
{
    use HasCategories;

    /** Kind of research, mapped to its human label. */
    public const KINDS = [
        'project' => 'Research project',
        'field_study' => 'Field study',
        'baseline' => 'Baseline study',
        'evaluation' => 'Evaluation',
        'policy_research' => 'Policy research',
    ];

    public const STATUSES = [
        'planned' => 'Planned',
        'active' => 'Active',
        'completed' => 'Completed',
        'published' => 'Published',
        'archived' => 'Archived',
    ];

    protected $fillable = [
        'lead_user_id', 'origin_concept_id', 'title', 'slug', 'kind', 'status', 'summary', 'objectives',
        'methodology', 'research_questions', 'data_collection', 'findings',
        'start_date', 'end_date',
    ];

    public function originConcept(): BelongsTo
    {
        return $this->belongsTo(ResearchConcept::class, 'origin_concept_id');
    }

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Projects the public may see — anything past the planning stage. */
    public function scopePublic(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'completed', 'published']);
    }

    protected static function booted(): void
    {
        static::saving(function (ResearchProject $project): void {
            if (blank($project->slug)) {
                $project->slug = static::uniqueSlug($project->title);
            }
        });

        static::deleting(fn (ResearchProject $project) => $project->comments()->delete());
    }

    protected static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'project';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function kindLabel(): string
    {
        return self::KINDS[$this->kind] ?? $this->kind;
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_user_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'project_topic');
    }

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'project_region');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function references(): HasMany
    {
        return $this->hasMany(Reference::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function dataItems(): HasMany
    {
        return $this->hasMany(DataItem::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
