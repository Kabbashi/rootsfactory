<?php

namespace App\Filament\Resources\ResearchProjects\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $title = 'Team';

    /** Project-level roles for a team member. */
    private const ROLES = ['lead' => 'Lead', 'member' => 'Member', 'reader' => 'Reader'];

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('role')->options(self::ROLES)->default('member')->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->weight('medium'),
                TextColumn::make('title')->label('Position')->placeholder('—')->toggleable(),
                TextColumn::make('pivot.role')->label('Project role')->badge()
                    ->formatStateUsing(fn (?string $state): string => self::ROLES[$state] ?? $state),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('role')->options(self::ROLES)->default('member')->required(),
                    ]),
            ])
            ->recordActions([
                DetachAction::make(),
            ]);
    }
}
