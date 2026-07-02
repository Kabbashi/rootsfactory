<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Code extends Model
{
    protected $fillable = ['category_id', 'name', 'color', 'description'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function dataItems(): BelongsToMany
    {
        return $this->belongsToMany(DataItem::class, 'codings')
            ->withPivot('excerpt', 'user_id')
            ->withTimestamps();
    }

    /**
     * The research projects this code reaches, via the data items it codes.
     * This is a code's real link into the research work.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function projectTitles(): \Illuminate\Support\Collection
    {
        return $this->dataItems
            ->loadMissing('project')
            ->pluck('project.title')
            ->filter()
            ->unique()
            ->values();
    }

    /** Ideas this code is assigned to. */
    public function ideas(): MorphToMany
    {
        return $this->morphedByMany(Idea::class, 'codeable');
    }

    /** Research concepts this code is assigned to. */
    public function researchConcepts(): MorphToMany
    {
        return $this->morphedByMany(ResearchConcept::class, 'codeable');
    }

    /** Research projects this code is assigned to. */
    public function researchProjects(): MorphToMany
    {
        return $this->morphedByMany(ResearchProject::class, 'codeable');
    }

    /**
     * Short summary of what this code is assigned to, e.g.
     * "2 ideas · 1 concept · 3 projects". Uses loaded *_count when present.
     */
    public function assignmentSummary(): string
    {
        $ideas = $this->ideas_count ?? $this->ideas()->count();
        $concepts = $this->research_concepts_count ?? $this->researchConcepts()->count();
        $projects = $this->research_projects_count ?? $this->researchProjects()->count();

        $parts = [];
        if ($ideas) {
            $parts[] = $ideas . ' ' . str('idea')->plural($ideas);
        }
        if ($concepts) {
            $parts[] = $concepts . ' ' . str('concept')->plural($concepts);
        }
        if ($projects) {
            $parts[] = $projects . ' ' . str('project')->plural($projects);
        }

        return $parts === [] ? '—' : implode(' · ', $parts);
    }
}
