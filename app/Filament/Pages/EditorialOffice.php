<?php

namespace App\Filament\Pages;

use App\Models\Publication;
use App\Models\ResearchConcept;
use App\Models\ResearchProject;
use App\Models\Review;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

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

    /**
     * Research projects grouped by their status.
     *
     * @return array<string, \Illuminate\Support\Collection<int, ResearchProject>>
     */
    public function getProjectPipeline(): array
    {
        $byStatus = ResearchProject::query()
            ->with('lead')
            ->latest('updated_at')
            ->get()
            ->groupBy('status');

        return collect(ResearchProject::STATUSES)
            ->keys()
            ->mapWithKeys(fn (string $status): array => [$status => $byStatus->get($status, collect())])
            ->all();
    }

    /**
     * What the current user is collaborating on: concepts they authored or
     * offered to help with, and projects they lead or are a member of.
     *
     * @return array{concepts: \Illuminate\Support\Collection, projects: \Illuminate\Support\Collection}
     */
    public function getMyWork(): array
    {
        $me = auth()->id();

        $concepts = ResearchConcept::query()
            ->where('user_id', $me)
            ->orWhereHas('collaborationOffers', fn (Builder $q) => $q->where('user_id', $me))
            ->latest('updated_at')
            ->get();

        $projects = ResearchProject::query()
            ->where('lead_user_id', $me)
            ->orWhereHas('members', fn (Builder $q) => $q->where('users.id', $me))
            ->latest('updated_at')
            ->get();

        return ['concepts' => $concepts, 'projects' => $projects];
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
}
