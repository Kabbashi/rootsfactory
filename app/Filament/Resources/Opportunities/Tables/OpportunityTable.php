<?php

namespace App\Filament\Resources\Opportunities\Tables;

use App\Models\Opportunity;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Arr;

class OpportunityTable
{
    /**
     * @param  array<int, string>  $types  the type values this Center may show
     */
    public static function configure(Table $table, array $types): Table
    {
        return $table
            ->defaultSort('deadline', 'asc')
            ->columns([
                TextColumn::make('title')
                    ->weight('medium')
                    ->limit(60)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('type')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => Opportunity::TYPES[$state] ?? '—')
                    // Only worth a column when more than one type is in play.
                    ->visible(count($types) > 1),
                TextColumn::make('organisation')
                    ->label('Organisation')
                    ->placeholder('—')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('amount')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('deadline')
                    ->date()
                    ->placeholder('—')
                    ->sortable()
                    ->color(fn (?Opportunity $record): string => $record?->deadline
                        && $record->deadline->isPast() ? 'danger' : 'gray'),
                TextColumn::make('topic.name')
                    ->label('Topic')
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('region.name')
                    ->label('Region')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Opportunity::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'success',
                        'closed' => 'gray',
                        default => 'warning',
                    }),
                IconColumn::make('ai_suggested')
                    ->label('AI')
                    ->boolean()
                    ->trueIcon('heroicon-m-sparkles')
                    ->falseIcon('')
                    ->tooltip('AI-suggested lead — verify before relying on it')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(Arr::only(Opportunity::TYPES, $types))
                    ->visible(count($types) > 1),
                SelectFilter::make('status')
                    ->options(Opportunity::STATUSES),
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
