<?php

namespace App\Filament\Resources\ResearchConcepts\Pages;

use App\Filament\Resources\ResearchConcepts\ResearchConceptResource;
use Filament\Resources\Pages\CreateRecord;

class CreateResearchConcept extends CreateRecord
{
    protected static string $resource = ResearchConceptResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] ??= auth()->id();

        return $data;
    }
}
