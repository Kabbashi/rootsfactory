<?php

namespace App\Filament\RelationManagers;

use App\Models\Task;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Delegate and track tasks on an idea, concept or project. Shared across all
 * three, it hangs off the model's polymorphic `tasks` relation.
 */
class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Tasks';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(200)->columnSpanFull(),
            Select::make('assignee_id')->label('Assign to')->relationship('assignee', 'name')
                ->searchable()->preload()->helperText('The member responsible for this task.'),
            Select::make('status')->options(Task::STATUSES)->default('todo')->required(),
            DatePicker::make('due_at')->label('Due')->native(false),
            Select::make('collaborators')->label('Working on it with')->relationship('collaborators', 'name')
                ->multiple()->searchable()->preload(),
            Textarea::make('description')->rows(3)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_at', 'asc')
            ->columns([
                TextColumn::make('title')->weight('medium')->wrap()->searchable(),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (string $state): string => Task::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'done' => 'success',
                        'doing' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('assignee.name')->label('Assigned to')->placeholder('—'),
                TextColumn::make('creator.name')->label('Given by')->placeholder('—')->toggleable(),
                TextColumn::make('collaborators.name')->label('With')->badge()->color('gray')->separator(',')->toggleable(),
                TextColumn::make('due_at')->date()->placeholder('—')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(Task::STATUSES),
            ])
            ->headerActions([
                CreateAction::make()->mutateDataUsing(function (array $data): array {
                    $data['created_by'] ??= auth()->id();

                    return $data;
                }),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
