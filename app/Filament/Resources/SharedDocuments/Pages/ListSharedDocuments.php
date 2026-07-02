<?php

namespace App\Filament\Resources\SharedDocuments\Pages;

use App\Filament\Resources\SharedDocuments\SharedDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSharedDocuments extends ListRecords
{
    protected static string $resource = SharedDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New shared document'),
        ];
    }
}
