<?php

namespace App\Filament\Resources\SharedDocuments;

use App\Filament\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\SharedDocuments\Pages\CreateSharedDocument;
use App\Filament\Resources\SharedDocuments\Pages\EditSharedDocument;
use App\Filament\Resources\SharedDocuments\Pages\ListSharedDocuments;
use App\Filament\Resources\SharedDocuments\RelationManagers\VersionsRelationManager;
use App\Models\SharedDocument;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SharedDocumentResource extends Resource
{
    protected static ?string $model = SharedDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial Office';

    protected static ?string $navigationLabel = 'Shared documents';

    protected static ?string $modelLabel = 'Shared document';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(200)->columnSpanFull(),
            Placeholder::make('last_edit')
                ->label('Last edited')
                ->content(fn (?SharedDocument $record): string => $record && $record->updated_by
                    ? ($record->editor?->name ?? 'Someone') . ' · ' . $record->updated_at?->diffForHumans()
                    : 'Not saved yet')
                ->visible(fn (?SharedDocument $record): bool => (bool) $record),
            MarkdownEditor::make('body')
                ->label('Document')
                ->helperText('Everyone in the network can edit. Each save is snapshotted — see “Version history”.')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')->weight('medium')->searchable()->wrap(),
                TextColumn::make('editor.name')->label('Last edited by')->placeholder('—'),
                TextColumn::make('versions_count')->label('Versions')->counts('versions')->badge()->color('gray'),
                TextColumn::make('updated_at')->label('Updated')->since()->sortable(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            VersionsRelationManager::class,
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSharedDocuments::route('/'),
            'create' => CreateSharedDocument::route('/create'),
            'edit' => EditSharedDocument::route('/{record}/edit'),
        ];
    }
}
