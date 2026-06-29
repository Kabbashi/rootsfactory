<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard;
use Filament\Support\Icons\Heroicon;

/**
 * Executive Center — the leadership overview. It is the panel's dashboard
 * (keeping the auto-discovered widgets such as the latest-discussion feed),
 * relocated off the root so the Portal can be the front door.
 */
class ExecutiveCenter extends Dashboard
{
    protected static string $routePath = '/executive';

    protected static string|\UnitEnum|null $navigationGroup = 'Overview';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Overview';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;
}
