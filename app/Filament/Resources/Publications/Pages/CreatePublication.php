<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Publication;
use Filament\Resources\Pages\CreateRecord;

class CreatePublication extends CreateRecord
{
    protected static string $resource = PublicationResource::class;

    protected function afterCreate(): void
    {
        /** @var Publication $publication */
        $publication = $this->record;

        // The creator becomes the first author, and we snapshot version 1.
        if (auth()->id() && ! $publication->authors()->whereKey(auth()->id())->exists()) {
            $publication->authors()->attach(auth()->id(), ['role' => 'author', 'order' => 0]);
        }

        $version = $publication->versions()->create([
            'created_by' => auth()->id(),
            'version_no' => 1,
            'abstract' => $publication->abstract,
            'body' => $publication->body,
            'changelog' => 'Initial version',
        ]);

        $publication->forceFill(['current_version_id' => $version->id])->saveQuietly();
    }
}
