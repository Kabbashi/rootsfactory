<?php

namespace App\Filament\Resources\DataItems\Pages;

use App\Filament\Resources\DataItems\DataItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDataItem extends CreateRecord
{
    protected static string $resource = DataItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] ??= auth()->id();

        return $data;
    }
}
