<?php

namespace App\Filament\Resources\SharedDocuments\Pages;

use App\Filament\Resources\SharedDocuments\SharedDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSharedDocument extends EditRecord
{
    protected static string $resource = SharedDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
