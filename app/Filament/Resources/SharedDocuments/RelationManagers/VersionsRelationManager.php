<?php

namespace App\Filament\Resources\SharedDocuments\RelationManagers;

use App\Models\SharedDocumentVersion;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Read-only history of a shared document. Any member can restore an earlier
 * version — which itself becomes a new snapshot, so nothing is lost.
 */
class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    protected static ?string $title = 'Version history';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Saved')->since(),
                TextColumn::make('savedBy.name')->label('By')->placeholder('—'),
                TextColumn::make('body')->label('Preview')
                    ->formatStateUsing(fn (?string $state): string => Str::limit(strip_tags((string) Str::markdown($state ?? '')), 90))
                    ->wrap(),
            ])
            ->recordActions([
                Action::make('restore')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->requiresConfirmation()
                    ->modalDescription('Restore this version? The current text is snapshotted first, so nothing is lost.')
                    ->action(function (SharedDocumentVersion $record): void {
                        $this->getOwnerRecord()->update(['body' => $record->body]);

                        Notification::make()->title('Version restored')->success()->send();
                    }),
            ]);
    }
}
