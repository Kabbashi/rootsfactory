<?php

namespace App\Filament\Resources\Publications\Pages;

use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Publication;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPublication extends EditRecord
{
    protected static string $resource = PublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('snapshot')
                ->label('Save version')
                ->icon('heroicon-m-bookmark-square')
                ->schema([
                    Textarea::make('changelog')->label('What changed?')->rows(3)->required(),
                ])
                ->action(function (array $data): void {
                    /** @var Publication $publication */
                    $publication = $this->record;
                    $next = (int) ($publication->versions()->max('version_no') ?? 0) + 1;

                    $version = $publication->versions()->create([
                        'created_by' => auth()->id(),
                        'version_no' => $next,
                        'abstract' => $publication->abstract,
                        'body' => $publication->body,
                        'changelog' => $data['changelog'],
                    ]);

                    $publication->forceFill(['current_version_id' => $version->id])->saveQuietly();

                    Notification::make()->title("Saved as version {$next}")->success()->send();
                }),
            DeleteAction::make(),
        ];
    }
}
