<?php

namespace App\Filament\Resources\ResearchProjects\RelationManagers;

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

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Tasks';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(200)->columnSpanFull(),
            Select::make('assignee_id')->label('Assignee')->relationship('assignee', 'name')->searchable()->preload(),
            Select::make('status')->options(Task::STATUSES)->default('todo')->required(),
            DatePicker::make('due_at')->label('Due')->native(false),
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
                TextColumn::make('assignee.name')->label('Assignee')->placeholder('—'),
                TextColumn::make('due_at')->date()->placeholder('—')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(Task::STATUSES),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
