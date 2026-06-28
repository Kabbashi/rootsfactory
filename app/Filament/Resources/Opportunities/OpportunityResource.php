<?php

namespace App\Filament\Resources\Opportunities;

use App\Filament\Resources\Opportunities\Pages\CreateOpportunity;
use App\Filament\Resources\Opportunities\Pages\EditOpportunity;
use App\Filament\Resources\Opportunities\Pages\ListOpportunities;
use App\Filament\Resources\Opportunities\Schemas\OpportunityForm;
use App\Filament\Resources\Opportunities\Tables\OpportunityTable;
use App\Models\Opportunity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OpportunityResource extends Resource
{
    protected static ?string $model = Opportunity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|\UnitEnum|null $navigationGroup = 'Opportunity Center';

    protected static ?string $modelLabel = 'Opportunity';

    protected static ?string $pluralModelLabel = 'Tenders & partnerships';

    protected static ?int $navigationSort = 1;

    /** This Center only deals with tenders and partnerships. */
    protected static array $types = Opportunity::OPPORTUNITY_TYPES;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->opportunities();
    }

    public static function form(Schema $schema): Schema
    {
        return OpportunityForm::configure($schema, static::$types);
    }

    public static function table(Table $table): Table
    {
        return OpportunityTable::configure($table, static::$types);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOpportunities::route('/'),
            'create' => CreateOpportunity::route('/create'),
            'edit' => EditOpportunity::route('/{record}/edit'),
        ];
    }
}
