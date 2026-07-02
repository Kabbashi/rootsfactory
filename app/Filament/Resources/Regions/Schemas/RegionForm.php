<?php

namespace App\Filament\Resources\Regions\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RegionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Country / Region')
                    ->required()
                    ->maxLength(100)
                    ->helperText('The country or region under study — a single country (e.g. Kenya, Jordan) or a wider region (e.g. Sahel, East Africa). Not a team member\'s home region.'),
                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
