<?php

namespace App\Filament\Resources\Ideas\Tables;

use App\Models\Idea;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IdeasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('core_statement')
                    ->label('Core statement')
                    ->limit(70)
                    ->wrap()
                    ->placeholder('—'),
                TextColumn::make('visibility')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => $state === 'public' ? 'success' : 'gray'),
                TextColumn::make('user.name')
                    ->label('Author')
                    ->toggleable(),
                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->badge()
                    ->color('gray')
                    ->separator(',')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('visibility')->options(Idea::VISIBILITIES),
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
