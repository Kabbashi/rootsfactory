<?php

namespace App\Filament\Resources\ResearchConcepts\Pages;

use App\Filament\Resources\ResearchConcepts\ResearchConceptResource;
use App\Jobs\GenerateAiInsight;
use App\Services\CoThinker;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditResearchConcept extends EditRecord
{
    protected static string $resource = ResearchConceptResource::class;

    private function isLocked(): bool
    {
        return $this->record->isLockedFor(auth()->id());
    }

    public function getSubheading(): ?string
    {
        return $this->isLocked()
            ? '🔒 This concept is Final. Only the person who brought it in can change it.'
            : null;
    }

    /** No save/cancel buttons when the concept is locked. */
    protected function getFormActions(): array
    {
        return $this->isLocked() ? [] : parent::getFormActions();
    }

    /** Server-side guard against a locked save slipping through. */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        abort_if($this->isLocked(), 403, 'This concept is final and locked.');

        return $data;
    }

    protected function getHeaderActions(): array
    {
        if ($this->isLocked()) {
            return [];
        }

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
                Action::make('ai_expand')
                    ->label('Expand into a brief')
                    ->icon('heroicon-m-arrows-pointing-out')
                    ->action(fn () => $this->expandBrief()),
            ])
                ->label('Ask AI')
                ->icon('heroicon-m-sparkles')
                ->button(),
            DeleteAction::make(),
        ];
    }

    /**
     * Grow the body into a structured brief. The draft is loaded into the
     * editor but NOT saved — the author reviews and Saves it themselves, so
     * AI text never reaches the public site without a human in the loop.
     */
    protected function expandBrief(): void
    {
        $this->record->body = app(CoThinker::class)->expand($this->record);
        $this->refreshFormData(['body']);

        Notification::make()
            ->title('Draft expanded — review and Save')
            ->body('Roots Factory AI rewrote the body into a structured brief. Check it over, edit anything, then Save to keep it. Nothing is stored until you do.')
            ->success()
            ->send();
    }

    /**
     * Queue a co-thinker pass; its reply lands in the discussion thread.
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
