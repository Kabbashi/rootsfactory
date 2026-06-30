<?php

namespace App\Filament\Resources\ResearchConcepts\Pages;

use App\Filament\Resources\ResearchConcepts\ResearchConceptResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResearchConcepts extends ListRecords
{
    protected static string $resource = ResearchConceptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
