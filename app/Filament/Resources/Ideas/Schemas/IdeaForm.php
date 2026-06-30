<?php

namespace App\Filament\Resources\Ideas\Schemas;

use App\Models\Category;
use App\Models\Idea;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class IdeaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->helperText('A short name — one or two words.')
                    ->required()
                    ->maxLength(120)
                    ->columnSpanFull(),
                Textarea::make('core_statement')
                    ->label('Core statement')
                    ->helperText('The heart of the idea, in a sentence or two.')
                    ->rows(2)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Description')
                    ->rows(5)
                    ->columnSpanFull(),
                Radio::make('visibility')
                    ->options(Idea::VISIBILITIES)
                    ->default('personal')
                    ->required(),
                Select::make('categories')
                    ->label('Categories')
                    ->relationship('categories', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Category $record): string => $record->qualifiedName())
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Select::make('keywords')
                    ->label('Keywords')
                    ->relationship('keywords', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required()->unique('keywords', 'name'),
                    ])
                    ->helperText('Pick from existing keywords or add new ones.')
                    ->columnSpanFull(),
                Select::make('crossReferences')
                    ->label('Related ideas')
                    ->relationship(
                        'crossReferences',
                        'name',
                        fn (Builder $query, ?Idea $record) => $query
                            ->visibleTo(auth()->id())
                            ->when($record, fn (Builder $q) => $q->whereKeyNot($record->getKey())),
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('Link to other ideas — these become the connections in the idea map.')
                    ->columnSpanFull(),
            ]);
    }
}
