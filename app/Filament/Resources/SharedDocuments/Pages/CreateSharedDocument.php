<?php

namespace App\Filament\Resources\SharedDocuments\Pages;

use App\Filament\Resources\SharedDocuments\SharedDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSharedDocument extends CreateRecord
{
    protected static string $resource = SharedDocumentResource::class;
}
