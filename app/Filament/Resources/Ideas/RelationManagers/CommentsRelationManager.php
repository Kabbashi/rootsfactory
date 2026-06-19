<?php

namespace App\Filament\Resources\Ideas\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Diskussion';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('body')
                    ->label('Kommentar')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->defaultSort('created_at', 'asc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Von')
                    ->weight('medium'),
                TextColumn::make('body')
                    ->label('Kommentar')
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Wann')
                    ->since(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Kommentieren')
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($record): bool => $record->user_id === auth()->id()),
                DeleteAction::make()
                    ->visible(fn ($record): bool => $record->user_id === auth()->id()),
            ]);
    }
}
