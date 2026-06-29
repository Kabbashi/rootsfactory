<?php

namespace App\Filament\Resources\Codes;

use App\Filament\Resources\Codes\Pages\ManageCodes;
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
            Select::make('category_id')->label('Category')->relationship('category', 'name')->searchable()->preload(),
            ColorPicker::make('color'),
            Textarea::make('description')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')->toggleable(),
                TextColumn::make('name')->weight('medium')->searchable(),
                TextColumn::make('category.name')->label('Category')->badge()->placeholder('—'),
                TextColumn::make('category.theme.name')->label('Theme')->badge()->color('gray')->placeholder('—')->toggleable(),
                TextColumn::make('data_items_count')->label('Coded')->counts('dataItems')->badge()->color('gray'),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name'),
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
