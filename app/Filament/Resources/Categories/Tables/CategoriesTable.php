<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')
                    ->label('Category')
                    ->state(fn (Category $record): string => $record->qualifiedName())
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('parent.name')
                    ->label('Parent')
                    ->placeholder('— top level —')
                    ->toggleable(),
                TextColumn::make('children_count')
                    ->label('Sub-categories')
                    ->counts('children')
                    ->alignCenter()
                    ->badge(),
                TextColumn::make('codes_count')
                    ->label('Codes')
                    ->counts('codes')
                    ->alignCenter()
                    ->badge()
                    ->toggleable(),
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
