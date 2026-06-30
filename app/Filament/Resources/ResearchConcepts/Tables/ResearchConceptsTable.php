<?php

namespace App\Filament\Resources\ResearchConcepts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ResearchConceptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('pinned')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-s-bookmark')
                    ->falseIcon('')
                    ->tooltip('Pinned'),
                TextColumn::make('title')
                    ->label('Title')
                    ->weight('medium')
                    ->limit(70)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => \App\Models\ResearchConcept::TYPES[$state] ?? '—'),
                TextColumn::make('topic.name')
                    ->label('Topic')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('region.name')
                    ->label('Region')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'in_discussion' => 'In discussion',
                        'published' => 'Published',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'in_discussion' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('user.name')
                    ->label('By')
                    ->toggleable(),
                TextColumn::make('comments_count')
                    ->label('💬')
                    ->counts('comments')
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'in_discussion' => 'In discussion',
                        'published' => 'Published',
                    ]),
                SelectFilter::make('type')
                    ->options(\App\Models\ResearchConcept::TYPES),
                SelectFilter::make('topic')
                    ->relationship('topic', 'name'),
                SelectFilter::make('region')
                    ->relationship('region', 'name'),
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
