<?php

namespace App\Filament\Resources\ResearchConcepts\Pages;

use App\Filament\Resources\ResearchConcepts\ResearchConceptResource;
use App\Models\Keyword;
use Filament\Resources\Pages\CreateRecord;

class CreateResearchConcept extends CreateRecord
{
    protected static string $resource = ResearchConceptResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] ??= auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        Keyword::syncNames($this->record, $this->data['keyword_names'] ?? []);
    }
}
