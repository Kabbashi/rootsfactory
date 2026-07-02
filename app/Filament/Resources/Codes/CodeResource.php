<?php

namespace App\Filament\Resources\Codes;

use App\Filament\Resources\Codes\Pages\ManageCodes;
use App\Models\Category;
use App\Models\Code;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CodeResource extends Resource
{
    protected static ?string $model = Code::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Data Hub';

    protected static ?string $modelLabel = 'Code';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(150),
            Select::make('category_id')
                ->label('Category')
                ->relationship('category', 'name')
                ->getOptionLabelFromRecordUsing(fn (Category $record): string => $record->qualifiedName())
                ->searchable()
                ->preload()
                ->createOptionForm([
                    TextInput::make('name')->required()->maxLength(120),
                    Select::make('parent_id')
                        ->label('Parent category')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('— top-level category —'),
                ])
                ->helperText('From the shared category taxonomy — the same tree used across ideas, concepts and projects.'),
            ColorPicker::make('color'),
            Select::make('ideas')->label('Ideas')->relationship('ideas', 'name')
                ->multiple()->searchable()->preload()
                ->helperText('Which ideas this code applies to.'),
            Select::make('researchConcepts')->label('Research concepts')->relationship('researchConcepts', 'title')
                ->multiple()->searchable()->preload(),
            Select::make('researchProjects')->label('Research projects')->relationship('researchProjects', 'title')
                ->multiple()->searchable()->preload(),
            Textarea::make('description')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')->toggleable(),
                TextColumn::make('name')->weight('medium')->searchable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->state(fn (Code $record): ?string => $record->category?->qualifiedName())
                    ->placeholder('—'),
                TextColumn::make('data_items_count')->label('Coded items')->counts('dataItems')->badge()->color('gray'),
                TextColumn::make('assignment')
                    ->label('Assigned to')
                    ->state(fn (Code $record): string => $record->assignmentSummary())
                    ->badge()
                    ->color('success')
                    ->placeholder('—'),
            ])
            ->modifyQueryUsing(fn ($query) => $query->withCount(['ideas', 'researchConcepts', 'researchProjects']))
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name'),
                SelectFilter::make('ideas')->label('Idea')->relationship('ideas', 'name')->searchable()->preload(),
                SelectFilter::make('researchConcepts')->label('Concept')->relationship('researchConcepts', 'title')->searchable()->preload(),
                SelectFilter::make('researchProjects')->label('Project')->relationship('researchProjects', 'title')->searchable()->preload(),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCodes::route('/'),
        ];
    }
}
