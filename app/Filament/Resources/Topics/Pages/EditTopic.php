<?php

namespace App\Filament\Resources\Topics\Pages;

use App\Filament\Resources\Topics\TopicResource;
use App\Jobs\GenerateAiInsight;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTopic extends EditRecord
{
    protected static string $resource = TopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('ai_synthesize')
                    ->label('Synthesize ideas')
                    ->icon('heroicon-m-document-text')
                    ->action(fn () => $this->askAi('summarize')),
                Action::make('ai_red_team')
                    ->label('Red-team')
                    ->icon('heroicon-m-shield-exclamation')
                    ->action(fn () => $this->askAi('red_team')),
                Action::make('ai_related')
                    ->label('Find related topics')
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
     * Queue a co-thinker pass; its reply lands in the topic's discussion.
     */
    protected function askAi(string $mode): void
    {
        GenerateAiInsight::for($this->record, $mode);

        Notification::make()
            ->title('Roots Factory AI is thinking…')
            ->body('Its response will appear in the discussion below in a moment — reload to see it.')
            ->info()
            ->send();
    }
}
