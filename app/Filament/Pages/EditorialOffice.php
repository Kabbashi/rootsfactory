<?php

namespace App\Filament\Pages;

use App\Models\Publication;
use App\Models\ResearchConcept;
use App\Models\Review;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Editorial Office — the publication workflow at a glance. Editors see the
 * manuscripts at each stage; everyone sees the reviews assigned to them.
 */
class EditorialOffice extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial Office';

    protected static ?string $navigationLabel = 'Workflow Dashboard';

    protected static ?string $title = 'Workflow Dashboard';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.editorial-office';

    /** Pre-publication stages, in workflow order. */
    public const PIPELINE = ['draft', 'internal_review', 'peer_review', 'revision', 'copy_edit', 'approved'];

    /**
     * Manuscripts grouped by their editorial stage.
     *
     * @return array<string, \Illuminate\Support\Collection<int, Publication>>
     */
    public function getPipeline(): array
    {
        $byStage = Publication::query()
            ->whereIn('status', self::PIPELINE)
            ->with('authors')
            ->latest('updated_at')
            ->get()
            ->groupBy('status');

        return collect(self::PIPELINE)
            ->mapWithKeys(fn (string $stage): array => [$stage => $byStage->get($stage, collect())])
            ->all();
    }

    /**
     * Research concepts grouped by their stage (draft → in discussion → final).
     *
     * @return array<string, \Illuminate\Support\Collection<int, ResearchConcept>>
     */
    public function getConceptPipeline(): array
    {
        $byStatus = ResearchConcept::query()
            ->with('user')
            ->latest('updated_at')
            ->get()
            ->groupBy('status');

        return collect(ResearchConcept::STATUSES)
            ->mapWithKeys(fn (string $status): array => [$status => $byStatus->get($status, collect())])
            ->all();
    }

    /** Reviews assigned to the current user that still need attention. */
    public function getMyReviews()
    {
        return Review::query()
            ->where('reviewer_id', auth()->id())
            ->where('status', '!=', 'done')
            ->with('publication')
            ->orderBy('due_at')
            ->get();
    }

    public function getRecentlyPublished()
    {
        return Publication::query()->where('status', 'published')->latest('published_at')->limit(5)->get();
    }
}
