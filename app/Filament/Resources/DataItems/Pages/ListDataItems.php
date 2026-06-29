<?php

namespace App\Filament\Resources\DataItems\Pages;

use App\Filament\Resources\DataItems\DataItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDataItems extends ListRecords
{
    protected static string $resource = DataItemResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
