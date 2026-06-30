<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use App\Filament\Resources\Ideas\IdeaResource;
use App\Filament\Resources\Publications\PublicationResource;
use App\Filament\Resources\ResearchConcepts\ResearchConceptResource;
use App\Filament\Resources\ResearchProjects\ResearchProjectResource;
use App\Models\Document;
use App\Models\Idea;
use App\Models\Publication;
use App\Models\ResearchConcept;
use App\Models\ResearchProject;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Workspace-wide full-text search over Postgres tsvector columns. One query
 * runs against ideas, concepts, projects, the library and publications,
 * ranked by relevance; idea visibility is respected.
 */
class Search extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $navigationLabel = 'Search';

    protected static ?string $title = 'Search';

    protected static ?int $navigationSort = -1;

    protected string $view = 'filament.pages.search';

    public string $q = '';

    /**
     * Grouped search results.
     *
     * @return array<int, array{label: string, items: array<int, array{title: string, snippet: ?string, url: string}>}>
     */
    public function getResults(): array
    {
        $q = trim($this->q);
        if (Str::length($q) < 2) {
            return [];
        }

        $groups = [
            $this->group('Idea Pool', Idea::query()->visibleTo(auth()->id()), $q,
                fn (Idea $r) => $r->name,
                fn (Idea $r) => $r->core_statement,
                fn (Idea $r) => IdeaResource::getUrl('edit', ['record' => $r]),
            ),
            $this->group('Research Concepts', ResearchConcept::query(), $q,
                fn (ResearchConcept $r) => $r->title,
                fn (ResearchConcept $r) => $r->body,
                fn (ResearchConcept $r) => ResearchConceptResource::getUrl('edit', ['record' => $r]),
            ),
            $this->group('Research Projects', ResearchProject::query(), $q,
                fn (ResearchProject $r) => $r->title,
                fn (ResearchProject $r) => $r->summary,
                fn (ResearchProject $r) => ResearchProjectResource::getUrl('edit', ['record' => $r]),
            ),
            $this->group('Knowledge Library', Document::query(), $q,
                fn (Document $r) => $r->title ?: $r->original_name,
                fn (Document $r) => $r->description,
                fn (Document $r) => DocumentResource::getUrl('edit', ['record' => $r]),
            ),
            $this->group('Publications', Publication::query(), $q,
                fn (Publication $r) => $r->title,
                fn (Publication $r) => $r->abstract,
                fn (Publication $r) => PublicationResource::getUrl('edit', ['record' => $r]),
            ),
        ];

        return array_values(array_filter($groups, fn (array $g): bool => $g['items'] !== []));
    }

    /**
     * Run one ranked full-text query and shape the rows.
     *
     * @return array{label: string, items: array<int, array<string, mixed>>}
     */
    private function group(string $label, Builder $query, string $q, callable $title, callable $snippet, callable $url): array
    {
        $rows = $query
            ->whereRaw('search_vector @@ websearch_to_tsquery(\'english\', ?)', [$q])
            ->orderByRaw('ts_rank(search_vector, websearch_to_tsquery(\'english\', ?)) desc', [$q])
            ->limit(8)
            ->get();

        return [
            'label' => $label,
            'items' => $rows->map(fn ($r): array => [
                'title' => (string) $title($r),
                'snippet' => filled($snippet($r)) ? Str::limit(strip_tags((string) $snippet($r)), 140) : null,
                'url' => $url($r),
            ])->all(),
        ];
    }
}
