<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;

/**
 * A shared, reusable keyword. The same vocabulary is autocompleted across the
 * Idea Pool and Research Concepts via the HasKeywords trait / keywordables.
 */
class Keyword extends Model
{
    protected $fillable = ['name'];

    public function ideas(): MorphToMany
    {
        return $this->morphedByMany(Idea::class, 'keywordable');
    }

    public function researchConcepts(): MorphToMany
    {
        return $this->morphedByMany(ResearchConcept::class, 'keywordable');
    }

    /** How many entities use this keyword. */
    public function timesUsed(): int
    {
        return DB::table('keywordables')->where('keyword_id', $this->id)->count();
    }

    /**
     * Resolve a list of free-text names to keyword ids (creating any new ones)
     * and sync them onto the given model's keywords relation.
     *
     * @param  array<int, string>  $names
     */
    public static function syncNames(Model $model, array $names): void
    {
        $ids = collect($names)
            ->map(fn ($n) => trim((string) $n))
            ->filter()
            ->unique()
            ->map(fn (string $name) => static::firstOrCreate(['name' => $name])->id)
            ->all();

        $model->keywords()->sync($ids);
    }
}
