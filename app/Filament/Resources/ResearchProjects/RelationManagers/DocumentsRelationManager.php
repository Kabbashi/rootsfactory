<?php

namespace App\Filament\Resources\ResearchProjects\RelationManagers;

use App\Models\ProjectDocument;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Collaborative, versioned project documents. Each save that changes the body
 * snapshots a new version (handled in the model), so history is browsable.
 */
class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documents';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(200)->columnSpanFull(),
            Textarea::make('body')->label('Content (Markdown)')->rows(16)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')->weight('medium')->wrap()->searchable(),
                TextColumn::make('version_no')->label('Version')->badge()->color('gray'),
                TextColumn::make('author.name')->label('Created by')->placeholder('—')->toggleable(),
                TextColumn::make('updated_at')->label('Updated')->since(),
            ])
            ->headerActions([
                CreateAction::make()->mutateDataUsing(function (array $data): array {
                    $data['created_by'] = auth()->id();

                    return $data;
                }),
            ])
            ->recordActions([
                Action::make('history')
                    ->label('History')
                    ->icon('heroicon-m-clock')
                    ->modalHeading('Version history')
                    ->modalContent(fn (ProjectDocument $record) => view('filament.project-document-history', [
                        'versions' => $record->versions()->with('author')->get(),
                    ]))
                    ->modalSubmitAction(false),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
