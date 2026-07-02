<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Ideas\IdeaResource;
use App\Models\Idea;
use Filament\Actions\Action;
use Filament\Pages\Page;

/**
 * The Idea mindmap — a read-only view. In per-idea mode (?idea=ID) it renders
 * the idea's own fields (name, core statement, description, categories,
 * keywords, related ideas) as a fixed mind map. Empty fields still show their
 * label, greyed, as a placeholder. Without an id it shows the whole pool
 * linked by cross-references.
 */
class IdeaMap extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Idea Map';

    protected string $view = 'filament.pages.idea-map';

    public ?Idea $record = null;

    public function mount(): void
    {
        $id = request()->integer('idea');

        if ($id) {
            $this->record = Idea::query()->visibleTo(auth()->id())->findOrFail($id);
            static::$title = 'Mind map — ' . $this->record->name;
        }
    }

    protected function getHeaderActions(): array
    {
        if ($this->record) {
            return [
                Action::make('editIdea')
                    ->label('Open idea')
                    ->icon('heroicon-m-pencil-square')
                    ->color('gray')
                    ->url(IdeaResource::getUrl('edit', ['record' => $this->record])),
            ];
        }

        return [
            Action::make('table')
                ->label('Table view')
                ->icon('heroicon-m-table-cells')
                ->color('gray')
                ->url(IdeaResource::getUrl()),
            Action::make('new')
                ->label('New idea')
                ->icon('heroicon-m-plus')
                ->url(IdeaResource::getUrl('create')),
        ];
    }

    /**
     * @return array{nodes: array<int, array>, edges: array<int, array>, mode: string, root: ?string}
     */
    public function getGraph(): array
    {
        return $this->record ? $this->ideaGraph() : $this->poolGraph();
    }

    /**
     * A single idea's fields as a fixed mind map. Every table field is shown;
     * empty ones become grey placeholders carrying just the field label.
     */
    private function ideaGraph(): array
    {
        $idea = $this->record->loadMissing(['categories', 'keywords', 'crossReferences']);

        $nodes = [];
        $edges = [];

        // Core theme: the name.
        $nodes[] = $this->node('name', 'name', $idea->name ?: 'Idea', false);

        // Core statement (rectangle) hangs under the name.
        $coreEmpty = blank($idea->core_statement);
        $nodes[] = $this->node('core', 'core', $coreEmpty ? 'Core statement' : $idea->core_statement, $coreEmpty);
        $edges[] = $this->edge('name', 'core');

        // Description (trapezoid) hangs under the core statement.
        $descEmpty = blank($idea->description);
        $nodes[] = $this->node('desc', 'description', $descEmpty ? 'Description' : $idea->description, $descEmpty);
        $edges[] = $this->edge('core', 'desc');

        // Categories — spheres around the core.
        if ($idea->categories->isEmpty()) {
            $nodes[] = $this->node('cat-ph', 'category', 'Categories', true);
            $edges[] = $this->edge('core', 'cat-ph');
        } else {
            foreach ($idea->categories as $i => $category) {
                $id = 'cat-' . $category->id;
                $nodes[] = $this->node($id, 'category', $category->qualifiedName(), false);
                $edges[] = $this->edge('core', $id);
            }
        }

        // Keywords — spheres around the core.
        if ($idea->keywords->isEmpty()) {
            $nodes[] = $this->node('kw-ph', 'keyword', 'Keywords', true);
            $edges[] = $this->edge('core', 'kw-ph');
        } else {
            foreach ($idea->keywords as $keyword) {
                $id = 'kw-' . $keyword->id;
                $nodes[] = $this->node($id, 'keyword', $keyword->name, false);
                $edges[] = $this->edge('core', $id);
            }
        }

        // Related ideas — rectangles to the left of the idea.
        if ($idea->crossReferences->isEmpty()) {
            $nodes[] = $this->node('rel-ph', 'related', 'Related ideas', true);
            $edges[] = $this->edge('name', 'rel-ph');
        } else {
            foreach ($idea->crossReferences as $related) {
                $id = 'rel-' . $related->id;
                $nodes[] = $this->node($id, 'related', $related->name, false, IdeaMap::getUrl() . '?idea=' . $related->id);
                $edges[] = $this->edge('name', $id);
            }
        }

        return ['nodes' => $nodes, 'edges' => $edges, 'mode' => 'idea', 'root' => 'name'];
    }

    /** @return array<string, mixed> */
    private function node(string $id, string $kind, string $label, bool $placeholder, ?string $url = null): array
    {
        return ['data' => array_filter([
            'id' => $id,
            'kind' => $kind,
            'label' => (string) str($label)->limit(120),
            'placeholder' => $placeholder ? 1 : 0,
            'url' => $url,
        ], fn ($v) => $v !== null)];
    }

    /** @return array<string, mixed> */
    private function edge(string $source, string $target): array
    {
        return ['data' => ['id' => "e-{$source}-{$target}", 'source' => $source, 'target' => $target]];
    }

    /** The whole pool, ideas linked by cross-references. */
    private function poolGraph(): array
    {
        $ideas = Idea::query()
            ->visibleTo(auth()->id())
            ->with('crossReferences:id')
            ->get();

        $visibleIds = $ideas->pluck('id')->all();

        $nodes = $ideas->map(fn (Idea $idea): array => [
            'data' => [
                'id' => (string) $idea->id,
                'kind' => 'pool',
                'label' => $idea->name,
                'visibility' => $idea->visibility,
                'url' => IdeaMap::getUrl() . '?idea=' . $idea->id,
            ],
        ])->values()->all();

        $edges = [];
        foreach ($ideas as $idea) {
            foreach ($idea->crossReferences as $related) {
                if (! in_array($related->id, $visibleIds, true)) {
                    continue;
                }
                $a = min($idea->id, $related->id);
                $b = max($idea->id, $related->id);
                $edges["{$a}-{$b}"] = [
                    'data' => [
                        'id' => "e{$a}_{$b}",
                        'source' => (string) $a,
                        'target' => (string) $b,
                    ],
                ];
            }
        }

        return ['nodes' => $nodes, 'edges' => array_values($edges), 'mode' => 'pool', 'root' => null];
    }
}
