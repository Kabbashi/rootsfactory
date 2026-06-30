<?php

namespace App\Filament\Resources\Ideas;

use App\Filament\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\Ideas\Pages\CreateIdea;
use App\Filament\Resources\Ideas\Pages\EditIdea;
use App\Filament\Resources\Ideas\Pages\ListIdeas;
use App\Filament\Resources\Ideas\RelationManagers\CollaborationOffersRelationManager;
use App\Filament\Resources\Ideas\Schemas\IdeaForm;
use App\Filament\Resources\Ideas\Tables\IdeasTable;
use App\Models\Idea;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IdeaResource extends Resource
{
    protected static ?string $model = Idea::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static string|\UnitEnum|null $navigationGroup = 'Research Hub';

    protected static ?string $navigationLabel = 'Idea Pool';

    protected static ?string $modelLabel = 'Idea';

    protected static ?string $pluralModelLabel = 'Idea Pool';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return IdeaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IdeasTable::configure($table);
    }

    /** Everyone sees public ideas; personal ideas only their own author. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->id());
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
            CollaborationOffersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIdeas::route('/'),
            'create' => CreateIdea::route('/create'),
            'edit' => EditIdea::route('/{record}/edit'),
        ];
    }
}
