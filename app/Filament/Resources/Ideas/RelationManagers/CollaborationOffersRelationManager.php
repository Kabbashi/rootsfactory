<?php

namespace App\Filament\Resources\Ideas\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only list of people who offered to collaborate on the idea. Only the
 * idea's owner sees it; offers are created via the "Offer to collaborate"
 * action, not here.
 */
class CollaborationOffersRelationManager extends RelationManager
{
    protected static string $relationship = 'collaborationOffers';

    protected static ?string $title = 'Collaboration offers';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->getAttribute('user_id') === auth()->id();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')->label('From')->weight('medium'),
                TextColumn::make('message')->label('Message')->wrap()->placeholder('—'),
                TextColumn::make('created_at')->label('When')->since(),
            ]);
    }
}
