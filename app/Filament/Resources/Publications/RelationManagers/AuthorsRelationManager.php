<?php

namespace App\Filament\Resources\Publications\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuthorsRelationManager extends RelationManager
{
    protected static string $relationship = 'authors';

    protected static ?string $title = 'Authors';

    private const ROLES = [
        'author' => 'Author',
        'co_author' => 'Co-author',
        'editor' => 'Editor',
        'guest' => 'Guest author',
    ];

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('role')->options(self::ROLES)->default('author')->required(),
            TextInput::make('order')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('publication_author.order')
            ->columns([
                TextColumn::make('name')->weight('medium'),
                TextColumn::make('pivot.role')->label('Role')->badge()
                    ->formatStateUsing(fn (?string $state): string => self::ROLES[$state] ?? $state),
                TextColumn::make('pivot.order')->label('Order')->toggleable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('role')->options(self::ROLES)->default('author')->required(),
                        TextInput::make('order')->numeric()->default(0),
                    ]),
            ])
            ->recordActions([EditAction::make(), DetachAction::make()]);
    }
}
