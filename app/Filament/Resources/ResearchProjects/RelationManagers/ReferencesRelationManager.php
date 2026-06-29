<?php

namespace App\Filament\Resources\ResearchProjects\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReferencesRelationManager extends RelationManager
{
    protected static string $relationship = 'references';

    protected static ?string $title = 'References & literature';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(300)->columnSpanFull(),
            TextInput::make('authors')->maxLength(300),
            TextInput::make('year')->numeric()->minValue(1800)->maxValue(2100),
            TextInput::make('source')->label('Journal / source')->maxLength(300),
            TextInput::make('citation_key')->label('Citation key')->maxLength(100),
            TextInput::make('doi')->maxLength(200),
            TextInput::make('url')->url()->maxLength(500)->columnSpanFull(),
            Textarea::make('notes')->rows(3)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('year', 'desc')
            ->columns([
                TextColumn::make('title')->weight('medium')->wrap()->searchable()->limit(80),
                TextColumn::make('authors')->placeholder('—')->limit(40)->toggleable(),
                TextColumn::make('year')->placeholder('—')->sortable(),
                TextColumn::make('source')->placeholder('—')->limit(30)->toggleable(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
