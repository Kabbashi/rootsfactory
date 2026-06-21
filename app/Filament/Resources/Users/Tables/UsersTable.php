<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('title')
                    ->label('Title')
                    ->placeholder('—')
                    ->limit(40),
                TextColumn::make('role')
                    ->label('Role')
                    ->badge(),
                TextColumn::make('ideas_count')
                    ->label('Ideas')
                    ->counts('ideas')
                    ->alignCenter()
                    ->badge(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
