<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * A node in the shared, hierarchical taxonomy. The same category tree is used
 * by the Idea Pool, Research Concepts, Research Projects (via the HasCategories
 * trait / categorizables pivot) and by coding Codes (category_id).
 */
class Category extends Model
{
    protected $fillable = ['parent_id', 'name', 'description', 'sort'];

    protected $casts = [
        'sort' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    public function codes(): HasMany
    {
        return $this->hasMany(Code::class);
    }

    /** Ideas tagged with this category (via the shared categorizables pivot). */
    public function ideas(): MorphToMany
    {
        return $this->morphedByMany(Idea::class, 'categorizable');
    }

    /** Research concepts tagged with this category. */
    public function researchConcepts(): MorphToMany
    {
        return $this->morphedByMany(ResearchConcept::class, 'categorizable');
    }

    /** Research projects tagged with this category. */
    public function researchProjects(): MorphToMany
    {
        return $this->morphedByMany(ResearchProject::class, 'categorizable');
    }

    /**
     * Short summary of where this category is used, e.g.
     * "3 ideas · 1 concept · 2 projects". Relies on the *_count attributes
     * being loaded (withCount) — falls back to a live count otherwise.
     */
    public function usageSummary(): string
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

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /** Full path, e.g. "Governance › Local administration › Corruption". */
    public function qualifiedName(): string
    {
        return $this->parent
            ? $this->parent->qualifiedName() . ' › ' . $this->name
            : $this->name;
    }
}
