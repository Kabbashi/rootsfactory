<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile')->schema([
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
                    Select::make('role')
                        ->options(User::ROLES)
                        ->required()
                        // Only editors/admins may change roles.
                        ->disabled(fn (): bool => ! (auth()->user()?->isEditor() ?? false))
                        ->dehydrated(fn (): bool => auth()->user()?->isEditor() ?? false),
                    Textarea::make('bio')
                        ->label('Bio')
                        ->rows(5)
                        ->maxLength(2000)
                        ->columnSpanFull()
                        ->helperText('A short public biography. Appears on your member page.'),
                ])->columns(2),
                Section::make('Scholarly profile')->schema([
                    TagsInput::make('expertise')->label('Areas of expertise')->placeholder('Add a field')->columnSpanFull(),
                    TagsInput::make('country_experience')->label('Country experience')->placeholder('Add a country')->columnSpanFull(),
                    TagsInput::make('languages')->placeholder('Add a language'),
                    TagsInput::make('method_competencies')->label('Methodological competencies')->placeholder('Add a method'),
                    Toggle::make('profile_public')
                        ->label('Show my profile publicly')
                        ->helperText('Off by default. When on, your name and profile may appear in the public Community directory and as a byline on your published work.')
                        ->columnSpanFull(),
                ])->columns(2)->collapsible(),
                Section::make('Links')->schema([
                    TextInput::make('linkedin')
                        ->label('LinkedIn')
                        ->url()
                        ->prefixIcon('heroicon-m-link')
                        ->placeholder('https://www.linkedin.com/in/…')
                        ->maxLength(255),
                    TextInput::make('instagram')
                        ->label('Instagram')
                        ->url()
                        ->prefixIcon('heroicon-m-link')
                        ->placeholder('https://www.instagram.com/…')
                        ->maxLength(255),
                ])->columns(2)->collapsible(),
                Section::make('Password')
                    ->description('Leave blank to keep your current password.')
                    ->schema([
                        TextInput::make('password')
                            ->label('New password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->minLength(8)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->confirmed()
                            ->maxLength(255),
                        TextInput::make('password_confirmation')
                            ->label('Confirm new password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->dehydrated(false)
                            ->maxLength(255),
                    ])->columns(2)->collapsible()
                    // Only the owner (or an admin) sets a password here.
                    ->visible(fn ($record): bool => $record !== null
                        && (auth()->id() === $record->getKey() || (auth()->user()?->isEditor() ?? false))),
            ]);
    }
}
