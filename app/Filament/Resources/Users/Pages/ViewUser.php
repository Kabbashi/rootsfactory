<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * The full member profile, readable by everyone in the network. Editing stays
 * gated to the profile's owner (and editors) via UserResource::canEdit().
 */
class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextEntry::make('name')->weight('bold')->size('lg'),
                TextEntry::make('title')->label('Title / affiliation')->placeholder('—'),
                TextEntry::make('bio')
                    ->label('Biography')
                    ->placeholder('No biography yet.')
                    ->prose()
                    ->columnSpanFull(),
            ])->columns(2),
            Section::make('Scholarly profile')->schema([
                TextEntry::make('expertise')->label('Areas of expertise')->badge()->placeholder('—'),
                TextEntry::make('country_experience')->label('Country experience')->badge()->placeholder('—'),
                TextEntry::make('languages')->badge()->placeholder('—'),
                TextEntry::make('method_competencies')->label('Methodological competencies')->badge()->placeholder('—'),
            ])->columns(2),
            Section::make('Links')->schema([
                TextEntry::make('linkedin')
                    ->label('LinkedIn')
                    ->placeholder('—')
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->color('primary'),
                TextEntry::make('instagram')
                    ->label('Instagram')
                    ->placeholder('—')
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->color('primary'),
                RepeatableEntry::make('links')
                    ->label('More links')
                    ->schema([
                        TextEntry::make('label')->hiddenLabel()
                            ->url(fn ($state, $record) => null),
                        TextEntry::make('url')->hiddenLabel()
                            ->url(fn (?string $state): ?string => $state)
                            ->openUrlInNewTab()
                            ->color('primary'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn ($record): bool => filled($record->links)),
            ])->columns(2),
        ]);
    }
}
