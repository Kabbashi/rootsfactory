<?php

namespace App\Filament\Resources\ResearchProjects\Pages;

use App\Filament\Resources\ResearchProjects\ResearchProjectResource;
use App\Models\ResearchProject;
use Filament\Resources\Pages\CreateRecord;

class CreateResearchProject extends CreateRecord
{
    protected static string $resource = ResearchProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['lead_user_id'] ??= auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var ResearchProject $project */
        $project = $this->record;

        // The lead joins their own project's team automatically.
        if ($project->lead_user_id && ! $project->members()->whereKey($project->lead_user_id)->exists()) {
            $project->members()->attach($project->lead_user_id, ['role' => 'lead']);
        }
    }
}
