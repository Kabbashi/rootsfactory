<?php

namespace App\Filament\Resources\Ideas\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class IdeaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Titel')
                    ->required()
                    ->maxLength(200)
                    ->columnSpanFull(),
                Select::make('topic_id')
                    ->label('Thema')
                    ->relationship('topic', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                    ]),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Entwurf',
                        'in_discussion' => 'In Diskussion',
                        'published' => 'Veröffentlicht',
                    ])
                    ->default('draft')
                    ->required(),
                Toggle::make('pinned')
                    ->label('Angepinnt')
                    ->helperText('Oben im Feed halten'),
                MarkdownEditor::make('body')
                    ->label('Inhalt')
                    ->columnSpanFull(),
            ]);
    }
}
