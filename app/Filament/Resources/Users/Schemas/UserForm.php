<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(120),
                TextInput::make('title')
                    ->label('Title / affiliation')
                    ->maxLength(150)
                    ->helperText('Shown under your name on your public profile, e.g. "Researcher, conceptnote".'),
                TextInput::make('email')
                    ->label('Email')
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('bio')
                    ->label('Bio')
                    ->rows(5)
                    ->maxLength(2000)
                    ->columnSpanFull()
                    ->helperText('A short public biography. Appears on your author page.'),
            ]);
    }
}
