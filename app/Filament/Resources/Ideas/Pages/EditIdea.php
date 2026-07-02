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
