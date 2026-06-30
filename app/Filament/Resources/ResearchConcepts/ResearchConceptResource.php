<?php

namespace App\Filament\Resources\ResearchConcepts;

use App\Filament\Resources\ResearchConcepts\Pages\CreateResearchConcept;
use App\Filament\Resources\ResearchConcepts\Pages\EditResearchConcept;
use App\Filament\Resources\ResearchConcepts\Pages\ListResearchConcepts;
use App\Filament\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\ResearchConcepts\Schemas\ResearchConceptForm;
use App\Filament\Resources\ResearchConcepts\Tables\ResearchConceptsTable;
use App\Models\ResearchConcept;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ResearchConceptResource extends Resource
{
    protected static ?string $model = ResearchConcept::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static string|\UnitEnum|null $navigationGroup = 'Research Hub';

    protected static ?string $modelLabel = 'Research concept';

    protected static ?string $pluralModelLabel = 'Research Concepts';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ResearchConceptForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResearchConceptsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
            \App\Filament\Resources\Ideas\RelationManagers\CollaborationOffersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResearchConcepts::route('/'),
            'create' => CreateResearchConcept::route('/create'),
            'edit' => EditResearchConcept::route('/{record}/edit'),
        ];
    }
}
