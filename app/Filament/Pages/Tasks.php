<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Ideas\IdeaResource;
use App\Filament\Resources\ResearchConcepts\ResearchConceptResource;
use App\Filament\Resources\ResearchProjects\ResearchProjectResource;
use App\Models\Idea;
use App\Models\ResearchConcept;
use App\Models\ResearchProject;
use App\Models\Task;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

/**
 * A Planner-style board of my tasks — the ones assigned to me and the ones I
 * have delegated to others — grouped into Idea, Concept and Project buckets.
 */
class Tasks extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial Office';

    protected static ?string $navigationLabel = 'Tasks';

    protected static ?string $title = 'Tasks';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.tasks';

    /** Subject types offered when creating a task. */
    private const SUBJECTS = [
        'idea' => Idea::class,
        'concept' => ResearchConcept::class,
        'project' => ResearchProject::class,
    ];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newTask')
                ->label('New task')
                ->icon('heroicon-m-plus')
                ->schema([
                    Select::make('subject_type')
                        ->label('About')
                        ->options(Task::BUCKETS)
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (callable $set) => $set('subject_id', null)),
                    Select::make('subject_id')
                        ->label('Which one')
                        ->options(fn (Get $get): array => $this->subjectOptions($get('subject_type')))
                        ->searchable()
                        ->required()
                        ->visible(fn (Get $get): bool => filled($get('subject_type'))),
                    TextInput::make('title')->required()->maxLength(200)->columnSpanFull(),
                    Select::make('assignee_id')->label('Assign to')
                        ->options(fn (): array => \App\Models\User::query()->where('role', '!=', 'system')->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->default(auth()->id())
                        ->helperText('Yourself or another member.'),
                    Select::make('status')->options(Task::STATUSES)->default('todo')->required(),
                    DatePicker::make('due_at')->label('Due')->native(false),
                    Select::make('collaborators')->label('Working on it with')
                        ->options(fn (): array => \App\Models\User::query()->where('role', '!=', 'system')->orderBy('name')->pluck('name', 'id')->all())
                        ->multiple()->searchable()->columnSpanFull(),
                    Textarea::make('description')->rows(3)->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $type = self::SUBJECTS[$data['subject_type']] ?? null;
                    if (! $type) {
                        return;
                    }

                    $task = Task::create([
                        'taskable_type' => $type,
                        'taskable_id' => $data['subject_id'],
                        'title' => $data['title'],
                        'assignee_id' => $data['assignee_id'] ?: null,
                        'created_by' => auth()->id(),
                        'status' => $data['status'],
                        'due_at' => $data['due_at'] ?? null,
                        'description' => $data['description'] ?? null,
                    ]);

                    if (! empty($data['collaborators'])) {
                        $task->collaborators()->sync($data['collaborators']);
                    }

                    Notification::make()->title('Task created')->success()->send();
                }),
        ];
    }

    /** @return array<int|string, string> */
    private function subjectOptions(?string $type): array
    {
        return match ($type) {
            'idea' => Idea::query()->visibleTo(auth()->id())->orderBy('name')->pluck('name', 'id')->all(),
            'concept' => ResearchConcept::query()->orderBy('title')->pluck('title', 'id')->all(),
            'project' => ResearchProject::query()->orderBy('title')->pluck('title', 'id')->all(),
            default => [],
        };
    }

    /**
     * My tasks (assigned to me or delegated by me), grouped into buckets.
     *
     * @return array<string, \Illuminate\Support\Collection<int, Task>>
     */
    public function getBoard(): array
    {
        $me = auth()->id();

        $tasks = Task::query()
            ->where(fn ($q) => $q->where('assignee_id', $me)->orWhere('created_by', $me))
            ->with(['taskable', 'assignee', 'creator', 'collaborators'])
            ->orderByRaw('due_at is null, due_at asc')
            ->get();

        $buckets = [];
        foreach (array_keys(Task::BUCKETS) as $key) {
            $buckets[$key] = $tasks->filter(fn (Task $t): bool => $t->bucket() === $key)->values();
        }

        return $buckets;
    }

    /** Edit URL of a task's subject, for the "Open" link on a card. */
    public function subjectUrl(Task $task): ?string
    {
        $subject = $task->taskable;

        if (! $subject) {
            return null;
        }

        return match ($task->bucket()) {
            'idea' => IdeaResource::getUrl('edit', ['record' => $subject]),
            'concept' => ResearchConceptResource::getUrl('edit', ['record' => $subject]),
            'project' => ResearchProjectResource::getUrl('edit', ['record' => $subject]),
            default => null,
        };
    }
}
