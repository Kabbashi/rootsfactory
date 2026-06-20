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
                    ->label('Title')
                    ->required()
                    ->maxLength(200)
                    ->columnSpanFull(),
                Select::make('topic_id')
                    ->label('Topic')
                    ->relationship('topic', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                    ]),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'in_discussion' => 'In discussion',
                        'published' => 'Published',
                    ])
                    ->default('draft')
                    ->required(),
                Toggle::make('pinned')
                    ->label('Pinned')
                    ->helperText('Keep at the top of the feed'),
                MarkdownEditor::make('body')
                    ->label('Content')
                    ->columnSpanFull(),
            ]);
    }
}
