<?php

namespace App\Filament\Resources\Topics\Tables;

use App\Models\Topic;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TopicsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->modifyQueryUsing(fn ($query) => $query->withCount(['researchConcepts', 'researchProjects']))
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('stages')
                    ->label('Appears in')
                    ->state(fn (Topic $record): array => $record->stages())
                    ->badge()
                    ->color('info')
                    ->placeholder('— not yet used —'),
                TextColumn::make('research_concepts_count')
                    ->label('Concepts')
                    ->alignCenter()
                    ->badge()
                    ->toggleable(),
                TextColumn::make('research_projects_count')
                    ->label('Projects')
                    ->alignCenter()
                    ->badge()
                    ->toggleable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(60)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('stage')
                    ->label('Appears in')
                    ->options([
                        'concept' => 'Concepts',
                        'project' => 'Research projects',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'concept' => $query->has('researchConcepts'),
                            'project' => $query->has('researchProjects'),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
