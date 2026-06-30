<?php

namespace App\Filament\Resources\Ideas\Pages;

use App\Filament\Pages\IdeaMap;
use App\Filament\Resources\Ideas\IdeaResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIdeas extends ListRecords
{
    protected static string $resource = IdeaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('map')
                ->label('Map view')
                ->icon('heroicon-m-share')
                ->color('gray')
                ->url(IdeaMap::getUrl()),
            CreateAction::make(),
        ];
    }
}
