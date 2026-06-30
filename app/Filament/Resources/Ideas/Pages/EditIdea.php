<?php

namespace App\Filament\Resources\Ideas\Pages;

use App\Filament\Resources\Ideas\IdeaResource;
use App\Models\Idea;
use App\Services\CoThinker;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditIdea extends EditRecord
{
    protected static string $resource = IdeaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('coreFromImage')
                ->label('Suggest core statement from image')
                ->icon('heroicon-m-sparkles')
                ->color('primary')
                ->visible(fn (): bool => filled($this->record->image_path))
                ->requiresConfirmation()
                ->modalDescription('AI will read the uploaded image and propose a core statement. You can edit it before saving.')
                ->action(function (CoThinker $ai): void {
                    /** @var Idea $idea */
                    $idea = $this->record;

                    try {
                        $suggestion = $ai->coreStatementFromImage(
                            Storage::disk('public')->path($idea->image_path),
                        );
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Could not read the image')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    // Form fields bind to $this->data; updating it reflects in the UI.
                    $this->data['core_statement'] = $suggestion;

                    Notification::make()
                        ->title('AI suggested a core statement')
                        ->body('Review and edit it, then save.')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
