<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Ideas\IdeaResource;
use App\Filament\Resources\ResearchConcepts\ResearchConceptResource;
use App\Filament\Resources\ResearchProjects\ResearchProjectResource;
use App\Models\Idea;
use App\Models\ResearchConcept;
use App\Models\ResearchProject;
use App\Services\CoThinker;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Alice AI — an interactive workspace to think with Roots Factory AI. Ask a
 * question or sketch something, get a reply, and save the draft where you want
 * it: as a new idea in the Idea Pool, a Research Concept (optionally based on an
 * existing idea), or a Research Project. Nothing is published automatically.
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

    /** Where Alice's draft should go: idea | concept | project. */
    public string $destination = 'idea';

    /** Optional idea (by id) a concept should be based on. */
    public ?int $basedOnIdeaId = null;

    /** Destinations offered in the dropdown. */
    public function destinations(): array
    {
        return [
            'idea' => 'New idea (Idea Pool)',
            'concept' => 'New research concept',
            'project' => 'New research project',
        ];
    }

    /** Visible ideas, to base a concept on. */
    public function ideaOptions(): array
    {
        return Idea::query()->visibleTo(auth()->id())->orderBy('name')->pluck('name', 'id')->all();
    }

    /** Ask Alice. Runs synchronously — the reply shows inline. */
    public function think(): void
    {
        $prompt = trim($this->prompt);

        if ($prompt === '') {
            Notification::make()->title('Write something to think about first.')->warning()->send();

            return;
        }

        // When basing a concept on an existing idea, give Alice its context.
        if ($this->destination === 'concept' && $this->basedOnIdeaId) {
            $idea = Idea::find($this->basedOnIdeaId);
            if ($idea) {
                $prompt = "Base your thinking on this existing idea from our pool:\n"
                    . "Idea: {$idea->name}\n"
                    . ($idea->core_statement ? "Core statement: {$idea->core_statement}\n" : '')
                    . ($idea->description ? "Description: {$idea->description}\n" : '')
                    . "\nRequest:\n" . $prompt;
            }
        }

        try {
            $this->answer = app(CoThinker::class)->brainstorm($prompt);
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('Alice is unavailable right now')
                ->body('Could not reach the AI gateway. Please try again later.')
                ->danger()
                ->send();
        }
    }

    /** Save Alice's reply as a draft in the chosen destination. */
    public function saveDraft(): void
    {
        if (blank($this->answer)) {
            return;
        }

        match ($this->destination) {
            'concept' => $this->saveAsConcept(),
            'project' => $this->saveAsProject(),
            default => $this->saveAsIdea(),
        };
    }

    private function titleFromPrompt(): string
    {
        return str(trim($this->prompt))->limit(120)->value() ?: 'Untitled draft';
    }

    private function saveAsIdea(): void
    {
        $idea = Idea::create([
            'user_id' => auth()->id(),
            'name' => str(trim($this->prompt))->limit(80)->value() ?: 'Untitled idea',
            'core_statement' => str(trim($this->prompt))->limit(200)->value(),
            'description' => $this->answer,
            'visibility' => 'personal',
        ]);

        $this->notifyAndOpen('Saved as a draft idea', IdeaResource::getUrl('edit', ['record' => $idea]));
    }

    private function saveAsConcept(): void
    {
        $baseIdea = $this->basedOnIdeaId ? Idea::find($this->basedOnIdeaId) : null;

        $concept = ResearchConcept::create([
            'user_id' => auth()->id(),
            'origin_idea_id' => $baseIdea?->id,
            'title' => $baseIdea?->name ?: $this->titleFromPrompt(),
            'body' => $this->answer,
            'type' => 'brief',
            'status' => 'draft',
        ]);

        // Carry over the idea's categories and keywords, like "move to concept".
        if ($baseIdea) {
            $concept->categories()->sync($baseIdea->categories->pluck('id'));
            $concept->keywords()->sync($baseIdea->keywords->pluck('id'));
        }

        $this->notifyAndOpen('Saved as a draft concept', ResearchConceptResource::getUrl('edit', ['record' => $concept]));
    }

    private function saveAsProject(): void
    {
        $project = ResearchProject::create([
            'lead_user_id' => auth()->id(),
            'title' => $this->titleFromPrompt(),
            'kind' => 'project',
            'status' => 'planned',
            'summary' => $this->answer,
        ]);

        $this->notifyAndOpen('Saved as a draft project', ResearchProjectResource::getUrl('edit', ['record' => $project]));
    }

    private function notifyAndOpen(string $title, string $url): void
    {
        Notification::make()->title($title)->body('Opened the new draft — review and refine it.')->success()->send();
        $this->reset(['prompt', 'answer', 'basedOnIdeaId']);
        $this->redirect($url);
    }
}
