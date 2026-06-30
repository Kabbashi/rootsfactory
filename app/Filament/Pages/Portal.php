<?php

namespace App\Filament\Pages;

use App\Filament\Resources\DataItems\DataItemResource;
use App\Filament\Resources\Documents\DocumentResource;
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
                'name' => 'Research',
                'description' => 'Projects, field studies, baselines and evaluations.',
                'icon' => 'heroicon-o-beaker',
                'url' => ResearchProjectResource::getUrl(),
            ],
            [
                'name' => 'Publications',
                'description' => 'Papers, briefs, essays — with versions and review.',
                'icon' => 'heroicon-o-document-text',
                'url' => PublicationResource::getUrl(),
            ],
            [
                'name' => 'Data Hub',
                'description' => 'Transcripts, field notes and qualitative coding.',
                'icon' => 'heroicon-o-clipboard-document-list',
                'url' => DataItemResource::getUrl(),
            ],
            [
                'name' => 'Knowledge Library',
                'description' => 'Methods, instruments, frameworks and templates.',
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
                'description' => 'The path from draft to published.',
                'icon' => 'heroicon-o-clipboard-document-check',
                'url' => EditorialOffice::getUrl(),
            ],
            [
                'name' => 'Research Q&A',
                'description' => 'Questions answered from our published briefs.',
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'url' => url('/ask'),
            ],
            [
                'name' => 'AI Assistant',
                'description' => 'Think with assistive AI — never decides for you.',
                'icon' => 'heroicon-o-cpu-chip',
                'url' => AgentCenter::getUrl(),
            ],
        ];
    }
}
