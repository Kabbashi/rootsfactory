<?php

namespace App\Filament\Pages;

use App\Filament\Resources\DataItems\DataItemResource;
use App\Filament\Resources\Documents\DocumentResource;
use App\Filament\Resources\Faqs\FaqResource;
use App\Filament\Resources\Publications\PublicationResource;
use App\Filament\Resources\ResearchProjects\ResearchProjectResource;
use App\Filament\Resources\Users\UserResource;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * The portal front door: a calm map of the workspace. Each tile leads to one
 * area of the research platform — from the collaborative project space to the
 * editorial office and the public-facing Q&A.
 */
class Portal extends Page
{
    protected static ?int $navigationSort = -2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Portal';

    protected static ?string $title = 'Rootsfactory Research Network';

    protected string $view = 'filament.pages.portal';

    /**
     * The areas of the workspace, in display order.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCenters(): array
    {
        return [
            [
                'name' => 'Research Hub',
                'description' => 'Ideas, concepts and research projects.',
                'icon' => 'heroicon-o-beaker',
                'url' => ResearchProjectResource::getUrl(),
            ],
            [
                'name' => 'Data Hub',
                'description' => 'Transcripts, field notes and qualitative coding. Overview of categories, keywords, topics and country/region.',
                'icon' => 'heroicon-o-clipboard-document-list',
                'url' => DataItemResource::getUrl(),
            ],
            [
                'name' => 'Publications',
                'description' => 'Papers, briefs, essays, baselines — final versions for publishing.',
                'icon' => 'heroicon-o-document-text',
                'url' => PublicationResource::getUrl(),
            ],
            [
                'name' => 'Knowledge Database',
                'description' => 'Knowledge management, references, metadata, methods, frameworks and templates.',
                'icon' => 'heroicon-o-book-open',
                'url' => DocumentResource::getUrl(),
            ],
            [
                'name' => 'Community',
                'description' => 'The network of researchers and their profiles.',
                'icon' => 'heroicon-o-user-group',
                'url' => UserResource::getUrl(),
            ],
            [
                'name' => 'Editorial Office',
                'description' => 'Workflow from idea generation to concept and research project. AI-assisted.',
                'icon' => 'heroicon-o-clipboard-document-check',
                'url' => EditorialOffice::getUrl(),
            ],
            [
                'name' => 'FAQ',
                'description' => 'How to think and write with R2N.',
                'icon' => 'heroicon-o-question-mark-circle',
                'url' => FaqResource::getUrl(),
            ],
            [
                'name' => 'Alice AI',
                'description' => 'Build your knowledge database with Alice AI. Automatic metadata extraction, summary, abstracts, suggested keywords, categories and topics.',
                'icon' => 'heroicon-o-cpu-chip',
                'url' => AgentCenter::getUrl(),
            ],
        ];
    }
}
