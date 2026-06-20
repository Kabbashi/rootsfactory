<?php

namespace App\Filament\Resources\Ideas\Pages;

use App\Filament\Resources\Ideas\IdeaResource;
use App\Jobs\GenerateAiInsight;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditIdea extends EditRecord
{
    protected static string $resource = IdeaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('ai_summarize')
                    ->label('Summarize')
                    ->icon('heroicon-m-document-text')
                    ->action(fn () => $this->askAi('summarize')),
                Action::make('ai_red_team')
                    ->label('Red-team')
                    ->icon('heroicon-m-shield-exclamation')
                    ->action(fn () => $this->askAi('red_team')),
                Action::make('ai_related')
                    ->label('Find related ideas')
                    ->icon('heroicon-m-link')
                    ->action(fn () => $this->askAi('related')),
            ])
                ->label('Ask AI')
                ->icon('heroicon-m-sparkles')
                ->button(),
            DeleteAction::make(),
        ];
    }

    /**
     * Queue a co-thinker pass; its reply lands in the discussion thread.
     */
    protected function askAi(string $mode): void
    {
        GenerateAiInsight::dispatch($this->record->getKey(), $mode);

        Notification::make()
            ->title('Roots Factory AI is thinking…')
            ->body('Its response will appear in the discussion below in a moment — reload to see it.')
            ->info()
            ->send();
    }
}
