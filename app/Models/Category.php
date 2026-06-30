<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
