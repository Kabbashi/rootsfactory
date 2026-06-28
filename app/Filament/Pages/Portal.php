<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Ideas\IdeaResource;
use App\Filament\Resources\Regions\RegionResource;
use App\Filament\Resources\Users\UserResource;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * The think-tank portal: the front door of the workspace. It presents the
 * eight Centers as tiles. Centers backed by real features link straight to
 * them; the rest are shown as "coming soon" so the map is honest about what
 * already works.
 */
class Portal extends Page
{
    protected static ?int $navigationSort = -2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Portal';

    protected static ?string $title = 'Roots Factory Portal';

    protected string $view = 'filament.pages.portal';

    /**
     * The eight Centers, in display order.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCenters(): array
    {
        return [
            [
                'name' => 'Executive Center',
                'description' => 'Overview, signals and decisions at a glance.',
                'icon' => 'heroicon-o-presentation-chart-line',
                'color' => 'emerald',
                'url' => ExecutiveCenter::getUrl(),
                'ready' => true,
            ],
            [
                'name' => 'Innovation Hub',
                'description' => 'Capture ideas, think out loud, shape briefs.',
                'icon' => 'heroicon-o-light-bulb',
                'color' => 'amber',
                'url' => IdeaResource::getUrl(),
                'ready' => true,
            ],
            [
                'name' => 'Research Center',
                'description' => 'Ask questions, get answers grounded in our briefs.',
                'icon' => 'heroicon-o-beaker',
                'color' => 'sky',
                'url' => url('/ask'),
                'ready' => true,
            ],
            [
                'name' => 'Knowledge Center',
                'description' => 'Published library, topics and regions.',
                'icon' => 'heroicon-o-book-open',
                'color' => 'indigo',
                'url' => RegionResource::getUrl(),
                'ready' => true,
            ],
            [
                'name' => 'Funding Center',
                'description' => 'Grants and funding opportunities.',
                'icon' => 'heroicon-o-banknotes',
                'color' => 'green',
                'url' => null,
                'ready' => false,
            ],
            [
                'name' => 'Opportunity Center',
                'description' => 'Tenders, calls and partnerships.',
                'icon' => 'heroicon-o-briefcase',
                'color' => 'orange',
                'url' => null,
                'ready' => false,
            ],
            [
                'name' => 'Agent Center',
                'description' => 'AI co-thinkers and automated assistants.',
                'icon' => 'heroicon-o-cpu-chip',
                'color' => 'violet',
                'url' => null,
                'ready' => false,
            ],
            [
                'name' => 'Board Room',
                'description' => 'People, governance and decisions.',
                'icon' => 'heroicon-o-user-group',
                'color' => 'rose',
                'url' => UserResource::getUrl(),
                'ready' => true,
            ],
        ];
    }
}
