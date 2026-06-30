<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Ideas\IdeaResource;
use App\Models\Idea;
use Filament\Actions\Action;
use Filament\Pages\Page;

/**
 * The Idea Pool as an interactive mindmap: every idea is a node, every
 * cross-reference an edge. Reached via a toggle from the Idea Pool table;
 * not registered in the navigation itself.
 */
class IdeaMap extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Idea Map';

    protected string $view = 'filament.pages.idea-map';

    protected function getHeaderActions(): array
    {
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
     * Nodes (visible ideas) and edges (cross-references between visible ideas).
     *
     * @return array{nodes: array<int, array>, edges: array<int, array>}
     */
    public function getGraph(): array
    {
        $ideas = Idea::query()
            ->visibleTo(auth()->id())
            ->with('crossReferences:id')
            ->get();

        $visibleIds = $ideas->pluck('id')->all();

        $nodes = $ideas->map(fn (Idea $idea): array => [
            'data' => [
                'id' => (string) $idea->id,
                'label' => $idea->name,
                'core' => (string) $idea->core_statement,
                'visibility' => $idea->visibility,
                'url' => IdeaResource::getUrl('edit', ['record' => $idea->getKey()]),
            ],
        ])->values()->all();

        $edges = [];
        foreach ($ideas as $idea) {
            foreach ($idea->crossReferences as $related) {
                if (! in_array($related->id, $visibleIds, true)) {
                    continue;
                }
                // De-duplicate undirected edges.
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

        return ['nodes' => $nodes, 'edges' => array_values($edges)];
    }
}
