<?php

namespace App\Filament\Resources\DataItems\Pages;

use App\Filament\Resources\DataItems\DataItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDataItem extends EditRecord
{
    protected static string $resource = DataItemResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
