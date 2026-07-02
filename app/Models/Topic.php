<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Topic extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    protected static function booted(): void
    {
        static::saving(function (Topic $topic) {
            if (blank($topic->slug)) {
                $topic->slug = Str::slug($topic->name);
            }
        });

        // Polymorphic comments have no DB-level cascade — clean them up here.
        static::deleting(fn (Topic $topic) => $topic->comments()->delete());
    }

    public function researchConcepts(): HasMany
    {
        return $this->hasMany(ResearchConcept::class);
    }

    public function researchProjects(): BelongsToMany
    {
        return $this->belongsToMany(ResearchProject::class, 'project_topic');
    }

    /**
     * Where this topic appears: 'concept', 'project', or both. Ideas in the
     * pool are not topic-tagged, so they are not part of this stage summary.
     *
     * @return array<int, string>
     */
    public function stages(): array
    {
        $conceptCount = $this->research_concepts_count ?? $this->researchConcepts()->count();
        $projectCount = $this->research_projects_count ?? $this->researchProjects()->count();

        $stages = [];
        if ($conceptCount) {
            $stages[] = 'Concept';
        }
        if ($projectCount) {
            $stages[] = 'Research project';
        }

        return $stages;
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
