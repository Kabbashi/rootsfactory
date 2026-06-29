<?php

namespace App\Filament\Resources\Codes\Pages;

use App\Filament\Resources\Codes\CodeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCodes extends ManageRecords
{
    protected static string $resource = CodeResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
