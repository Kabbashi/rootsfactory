<?php

namespace App\Filament\Resources\ResearchProjects\RelationManagers;

use App\Models\Coding;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

/**
 * Read-only evidence overview: every coded excerpt across the project's data
 * items, grouped by code. Coding itself happens on each data item.
 */
class EvidenceRelationManager extends RelationManager
{
    protected static string $relationship = 'codings';

    protected static ?string $title = 'Evidence';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('code_id')
            ->groups([
                Group::make('code.name')->label('Code'),
            ])
            ->defaultGroup('code.name')
            ->columns([
                TextColumn::make('excerpt')
                    ->wrap()
                    ->limit(220)
                    ->placeholder('— no excerpt —'),
                TextColumn::make('dataItem.title')
                    ->label('Source')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('code.category.name')
                    ->label('Category')
                    ->state(fn (Coding $record): ?string => $record->code?->category?->qualifiedName())
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('Coder')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('openSource')
                    ->label('Open source')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Coding $record): ?string => $record->dataItem
                        ? \App\Filament\Resources\DataItems\DataItemResource::getUrl('edit', ['record' => $record->dataItem])
                        : null),
            ]);
    }
}
