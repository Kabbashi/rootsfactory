<?php

namespace App\Filament\Resources\Funding;

use App\Filament\Resources\Funding\Pages\CreateFunding;
use App\Filament\Resources\Funding\Pages\EditFunding;
use App\Filament\Resources\Funding\Pages\ListFunding;
use App\Filament\Resources\Opportunities\Schemas\OpportunityForm;
use App\Filament\Resources\Opportunities\Tables\OpportunityTable;
use App\Models\Opportunity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * The Funding Center: grants and donor calls. Same model and shared
 * form/table as the Opportunity Center, scoped to grants.
 */
class FundingResource extends Resource
{
    protected static ?string $model = Opportunity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Funding Center';

    protected static ?string $modelLabel = 'Grant';

    protected static ?string $pluralModelLabel = 'Grants & funding';

    protected static ?int $navigationSort = 1;

    protected static array $types = Opportunity::FUNDING_TYPES;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->funding();
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
            'index' => ListFunding::route('/'),
            'create' => CreateFunding::route('/create'),
            'edit' => EditFunding::route('/{record}/edit'),
        ];
    }
}
