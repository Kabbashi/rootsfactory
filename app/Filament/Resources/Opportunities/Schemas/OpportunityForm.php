<?php

namespace App\Filament\Resources\Opportunities\Schemas;

use App\Models\Opportunity;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;

class OpportunityForm
{
    /**
     * @param  array<int, string>  $types  the type values this Center may use
     */
    public static function configure(Schema $schema, array $types): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(200)
                    ->columnSpanFull(),
                Select::make('type')
                    ->options(Arr::only(Opportunity::TYPES, $types))
                    ->default($types[0])
                    ->required()
                    // A single-type Center (Funding) has nothing to choose.
                    ->disabled(count($types) === 1)
                    ->dehydrated(),
                TextInput::make('organisation')
                    ->label('Organisation / donor')
                    ->maxLength(200),
                TextInput::make('amount')
                    ->label('Amount / value')
                    ->placeholder('e.g. €50,000–€200,000')
                    ->maxLength(100),
                DatePicker::make('deadline')
                    ->native(false),
                Select::make('topic_id')
                    ->label('Topic')
                    ->relationship('topic', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('region_id')
                    ->label('Region')
                    ->relationship('region', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Global / unspecified'),
                Select::make('status')
                    ->options(Opportunity::STATUSES)
                    ->default('open')
                    ->required(),
                TextInput::make('url')
                    ->label('Link')
                    ->url()
                    ->maxLength(500)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }
}
