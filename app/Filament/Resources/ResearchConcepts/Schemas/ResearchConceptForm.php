<?php

namespace App\Filament\Resources\ResearchConcepts\Schemas;

use App\Models\Category;
use App\Models\Keyword;
use App\Models\ResearchConcept;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ResearchConceptForm
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
                Select::make('region_id')
                    ->label('Region')
                    ->relationship('region', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Global / unspecified')
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                    ]),
                Select::make('type')
                    ->label('Type')
                    ->options(ResearchConcept::TYPES)
                    ->default('brief')
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options(ResearchConcept::STATUS_LABELS)
                    ->default('draft')
                    ->required()
                    ->helperText('Once Final, the concept is locked — only the person who brought it in can change it.'),
                Toggle::make('pinned')
                    ->label('Pinned')
                    ->helperText('Keep at the top of the feed'),
                Select::make('categories')
                    ->label('Categories')
                    ->relationship('categories', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Category $record): string => $record->qualifiedName())
                    ->multiple()
                    ->searchable()
                    ->preload(),
                TagsInput::make('keyword_names')
                    ->label('Keywords')
                    ->splitKeys([';'])
                    ->suggestions(Keyword::orderBy('name')->pluck('name')->all())
                    ->dehydrated(false)
                    ->helperText('Separate keywords with a semicolon.'),
                MarkdownEditor::make('body')
                    ->label('Content')
                    ->columnSpanFull(),
            ])
            // A final concept is read-only for everyone but its originator.
            ->disabled(fn (?ResearchConcept $record): bool => (bool) $record?->isLockedFor(auth()->id()));
    }
}
