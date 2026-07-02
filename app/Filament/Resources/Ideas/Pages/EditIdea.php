<?php

namespace App\Filament\Resources\Ideas\Pages;

use App\Filament\Pages\IdeaMap;
use App\Filament\Resources\Ideas\IdeaResource;
use App\Filament\Resources\ResearchConcepts\ResearchConceptResource;
use App\Models\Idea;
use App\Models\Keyword;
use App\Models\ResearchConcept;
use App\Services\CoThinker;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditIdea extends EditRecord
{
    protected static string $resource = IdeaResource::class;

    /** Hydrate the keyword tags from the relation. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['keyword_names'] = $this->record->keywords->pluck('name')->all();

        return $data;
    }

    protected function afterSave(): void
    {
        Keyword::syncNames($this->record, $this->data['keyword_names'] ?? []);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mindMap')
                ->label('Mind map')
                ->icon('heroicon-m-share')
                ->color('gray')
                ->url(fn (): string => IdeaMap::getUrl() . '?idea=' . $this->record->id),
            ActionGroup::make([
                Action::make('alice_expand')
                    ->label('Expand with Alice')
                    ->icon('heroicon-m-arrows-pointing-out')
                    ->action(function (CoThinker $ai): void {
                        $idea = $this->record;
                        $context = "Idea: {$idea->name}\n"
                            . 'Core statement: ' . ($idea->core_statement ?: '(none)') . "\n"
                            . 'Description: ' . ($idea->description ?: '(none)');

                        try {
                            $draft = $ai->assist(
                                'You are Alice, helping shape an early research idea. Research the topic and expand '
                                . 'it into a fuller description: what it is, why it matters, how it could work, and '
                                . 'open questions. Keep it a proposal, not a claim of fact.',
                                $context,
                                $this->ideaPdfText(),
                            );
                        } catch (\Throwable $e) {
                            report($e);
                            Notification::make()->title('Alice is unavailable right now')->danger()->send();

                            return;
                        }

                        $existing = trim((string) ($this->data['description'] ?? ''));
                        $this->data['description'] = $existing === '' ? $draft : $existing . "\n\n---\n\n" . $draft;

                        Notification::make()->title('Alice expanded the idea')
                            ->body('Review the description and Save.')->success()->send();
                    }),
                Action::make('alice_core')
                    ->label('Suggest core statement')
                    ->icon('heroicon-m-sparkles')
                    ->action(function (CoThinker $ai): void {
                        $idea = $this->record;
                        $context = "Idea name: {$idea->name}\nDescription: " . ($idea->description ?: '(none)');

                        try {
                            $draft = $ai->assist(
                                'Propose a concise one- or two-sentence core statement capturing the heart of this '
                                . 'idea. Return only the statement, no preamble.',
                                $context,
                                '',
                                200,
                            );
                        } catch (\Throwable $e) {
                            report($e);
                            Notification::make()->title('Alice is unavailable right now')->danger()->send();

                            return;
                        }

                        $this->data['core_statement'] = trim($draft);

                        Notification::make()->title('Alice suggested a core statement')
                            ->body('Review and Save.')->success()->send();
                    }),
            ])
                ->label('Ask Alice')
                ->icon('heroicon-m-sparkles')
                ->button()
                ->color('gray'),
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
            Action::make('moveToConcept')
                ->label('Move to Research Concept')
                ->icon('heroicon-m-arrow-right-circle')
                ->color('primary')
                ->requiresConfirmation()
                ->modalDescription('Create a Research Concept with the same name, carrying over this idea\'s text, categories and keywords. The idea stays in the pool.')
                ->action(function (): void {
                    /** @var Idea $idea */
                    $idea = $this->record;

                    $body = trim(
                        ($idea->core_statement ? $idea->core_statement . "\n\n" : '')
                        . (string) $idea->description
                    );

                    $concept = ResearchConcept::create([
                        'user_id' => $idea->user_id,
                        'origin_idea_id' => $idea->id,
                        'title' => $idea->name,
                        'body' => $body,
                        'type' => 'brief',
                        'status' => 'draft',
                    ]);

                    $concept->categories()->sync($idea->categories->pluck('id'));
                    $concept->keywords()->sync($idea->keywords->pluck('id'));

                    Notification::make()
                        ->title('Moved to a Research Concept')
                        ->body('Opened the new draft concept.')
                        ->success()
                        ->send();

                    $this->redirect(ResearchConceptResource::getUrl('edit', ['record' => $concept]));
                }),
            ...$this->reactionActions(),
            Action::make('collaborate')
                ->label('Offer to collaborate')
                ->icon('heroicon-m-hand-raised')
                ->color('primary')
                ->visible(fn (): bool => $this->record->isPublic() && $this->record->user_id !== auth()->id())
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
                }),
            DeleteAction::make(),
        ];
    }

    /** Text of the first PDF attached to the idea, for Alice to read. */
    private function ideaPdfText(): string
    {
        foreach ((array) ($this->record->attachments ?? []) as $path) {
            if (is_string($path) && str_ends_with(strtolower($path), '.pdf')) {
                return app(CoThinker::class)->pdfTextFromUpload($path);
            }
        }

        return '';
    }

    /**
     * Emoji reaction toggles, shown only on public ideas.
     *
     * @return array<int, \Filament\Actions\ActionGroup>
     */
    private function reactionActions(): array
    {
        if (! $this->record->isPublic()) {
            return [];
        }

        $userId = auth()->id();

        $buttons = [];
        foreach (Idea::EMOJIS as $emoji) {
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
}
