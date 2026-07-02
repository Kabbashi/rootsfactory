<?php

namespace App\Filament\Resources\ResearchConcepts\Pages;

use App\Filament\Resources\ResearchConcepts\ResearchConceptResource;
use App\Filament\Resources\ResearchProjects\ResearchProjectResource;
use App\Jobs\GenerateAiInsight;
use App\Models\Keyword;
use App\Services\CoThinker;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditResearchConcept extends EditRecord
{
    protected static string $resource = ResearchConceptResource::class;

    private function isLocked(): bool
    {
        return $this->record->isLockedFor(auth()->id());
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['keyword_names'] = $this->record->keywords->pluck('name')->all();

        return $data;
    }

    protected function afterSave(): void
    {
        Keyword::syncNames($this->record, $this->data['keyword_names'] ?? []);
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
        $actions = [];

        // Editing tools are hidden when the concept is final/locked.
        if (! $this->isLocked()) {
            $actions[] = ActionGroup::make([
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
                ->button();
        }

        // Concepts grown from public ideas carry the emoji reactions — allowed
        // even when final, since reacting is not editing.
        if ($this->record->isFromPublicIdea()) {
            $actions = array_merge($actions, $this->reactionActions());
        }

        // Anyone but the owner can offer to collaborate on a concept. The offer
        // (with its message) lands in the "Collaboration offers" tab for the
        // owner, and the concept shows up in the offerer's Workflow Dashboard.
        $actions[] = Action::make('collaborate')
            ->label('Offer to collaborate')
            ->icon('heroicon-m-hand-raised')
            ->color('primary')
            ->visible(fn (): bool => $this->record->user_id !== auth()->id())
            ->schema([
                Textarea::make('message')->label('Message (optional)')->rows(3),
            ])
            ->action(function (array $data): void {
                $this->record->collaborationOffers()->updateOrCreate(
                    ['user_id' => auth()->id()],
                    ['message' => $data['message'] ?? null],
                );

                Notification::make()
                    ->title('Your offer to collaborate was sent')
                    ->success()
                    ->send();
            });

        // Hand-off: grow a mature concept into a Research Project. Available to
        // the concept's owner and editors, even when the concept is Final —
        // spawning a project is not editing the concept.
        $actions[] = Action::make('spawnProject')
            ->label('Grow into a Research Project')
            ->icon('heroicon-m-rocket-launch')
            ->color('primary')
            ->visible(fn (): bool => $this->record->user_id === auth()->id() || (auth()->user()?->isEditor() ?? false))
            ->requiresConfirmation()
            ->modalDescription('Create a Research Project from this concept, carrying over its title, text and categories. If a project already exists for this concept, you’ll be taken to it. The concept itself stays unchanged.')
            ->action(function (): void {
                $project = $this->record->spawnResearchProject();

                Notification::make()
                    ->title($project->wasRecentlyCreated ? 'Project created from this concept' : 'Opened the existing project')
                    ->success()
                    ->send();

                $this->redirect(ResearchProjectResource::getUrl('edit', ['record' => $project]));
            });

        if (! $this->isLocked()) {
            $actions[] = DeleteAction::make();
        }

        return $actions;
    }

    /**
     * Emoji reaction toggles for concepts from public ideas.
     *
     * @return array<int, \Filament\Actions\ActionGroup>
     */
    private function reactionActions(): array
    {
        $userId = auth()->id();

        $buttons = [];
        foreach (\App\Models\ResearchConcept::EMOJIS as $emoji) {
            $buttons[] = Action::make('react_' . md5($emoji))
                ->label(fn (): string => $emoji . ' ' . $this->record->reactionCount($emoji))
                ->color(fn (): string => $this->record->hasReactionFrom($userId, $emoji) ? 'primary' : 'gray')
                ->action(function () use ($userId, $emoji): void {
                    $this->record->toggleReaction($userId, $emoji);
                    $this->record->refresh();
                });
        }

        return [
            ActionGroup::make($buttons)
                ->label('React')
                ->icon('heroicon-m-face-smile')
                ->button()
                ->color('gray'),
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
