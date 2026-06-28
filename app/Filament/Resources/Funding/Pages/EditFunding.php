<?php

namespace App\Filament\Resources\Funding\Pages;

use App\Filament\Resources\Funding\FundingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFunding extends EditRecord
{
    protected static string $resource = FundingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
