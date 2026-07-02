<?php

namespace App\Filament\Pages;

use App\Models\ResearchConcept;
use App\Services\CoThinker;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Agent Center — an interactive workspace to think with Roots Factory AI.
 * Ask a question or sketch something, get a reply, and optionally keep it as
 * a draft idea (never published automatically — a human reviews first).
 */
class AgentCenter extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial Office';

    protected static ?string $navigationLabel = 'Alice AI';

    protected static ?string $title = 'Alice AI';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.agent-center';

    public string $prompt = '';

    public ?string $answer = null;

    /** Ask the co-thinker. Runs synchronously — the reply shows inline. */
    public function think(): void
    {
        $prompt = trim($this->prompt);

        if ($prompt === '') {
            Notification::make()->title('Write something to think about first.')->warning()->send();

            return;
        }

        try {
            $this->answer = app(CoThinker::class)->brainstorm($prompt);
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('AI is unavailable right now')
                ->body('Could not reach the AI gateway. Please try again later.')
                ->danger()
                ->send();
        }
    }

    /** Keep the reply as a draft idea for the team to refine and discuss. */
    public function saveAsIdea(): void
    {
        if (blank($this->answer)) {
            return;
        }

        $idea = ResearchConcept::create([
            'user_id' => auth()->id(),
            'title' => str(trim($this->prompt))->limit(120)->value() ?: 'Untitled draft',
            'body' => $this->answer,
            'status' => 'draft',
            'type' => 'note',
        ]);

        Notification::make()
            ->title('Saved as a draft idea')
            ->body('Find it in the Innovation Hub to refine, discuss and (later) publish.')
            ->success()
            ->send();

        $this->reset(['prompt', 'answer']);

        $this->redirect(\App\Filament\Resources\ResearchConcepts\ResearchConceptResource::getUrl('edit', ['record' => $idea]));
    }
}
