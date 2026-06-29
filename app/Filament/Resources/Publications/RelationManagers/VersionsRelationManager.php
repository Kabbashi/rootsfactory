<?php

namespace App\Filament\Resources\Publications\RelationManagers;

use App\Models\PublicationVersion;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

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
            ->defaultSort('version_no', 'desc')
            ->columns([
                TextColumn::make('version_no')->label('Version')->badge()->color('gray'),
                TextColumn::make('changelog')->wrap()->placeholder('—')->limit(80),
                TextColumn::make('author.name')->label('By')->placeholder('—'),
                TextColumn::make('created_at')->label('When')->since(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->modalHeading(fn (PublicationVersion $record): string => "Version {$record->version_no}")
                    ->modalContent(fn (PublicationVersion $record) => view('filament.publication-version', [
                        'version' => $record,
                    ]))
                    ->modalSubmitAction(false),
            ]);
    }
}
