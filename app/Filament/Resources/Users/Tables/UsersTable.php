<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->columns([
                // Hidden but searchable, so the search bar still works over the cards.
                TextColumn::make('name')->searchable()->extraAttributes(['class' => 'hidden']),
                TextColumn::make('title')->searchable()->extraAttributes(['class' => 'hidden']),
                ViewColumn::make('card')->view('filament.tables.member-card'),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
