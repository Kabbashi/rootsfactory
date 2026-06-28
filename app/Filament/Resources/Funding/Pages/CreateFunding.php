<?php

namespace App\Filament\Resources\Funding\Pages;

use App\Filament\Resources\Funding\FundingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFunding extends CreateRecord
{
    protected static string $resource = FundingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'grant';
        $data['user_id'] ??= auth()->id();

        return $data;
    }
}
