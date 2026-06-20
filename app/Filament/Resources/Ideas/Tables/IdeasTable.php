<?php

namespace App\Filament\Resources\Ideas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IdeasTable
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
                TextColumn::make('topic.name')
                    ->label('Topic')
                    ->badge()
                    ->placeholder('—'),
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
                //
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
