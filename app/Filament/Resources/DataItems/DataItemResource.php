<?php

namespace App\Filament\Resources\DataItems;

use App\Filament\Resources\DataItems\Pages\CreateDataItem;
use App\Filament\Resources\DataItems\Pages\EditDataItem;
use App\Filament\Resources\DataItems\Pages\ListDataItems;
use App\Models\DataItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DataItemResource extends Resource
{
    protected static ?string $model = DataItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Data Hub';

    protected static ?string $modelLabel = 'Data item';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(250)->columnSpanFull(),
            Select::make('kind')->options(DataItem::KINDS)->default('transcript')->required(),
            Select::make('research_project_id')->label('Project')->relationship('project', 'title')->searchable()->preload(),
            TextInput::make('language')->default('en')->maxLength(8),
            DatePicker::make('collected_at')->label('Collection date')->native(false),
            Select::make('codes')->relationship('codes', 'name')->multiple()->searchable()->preload()
                ->helperText('Apply qualitative codes to this item.')->columnSpanFull(),
            Textarea::make('content')->rows(14)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('collected_at', 'desc')
            ->columns([
                TextColumn::make('title')->weight('medium')->wrap()->searchable()->limit(70),
                TextColumn::make('kind')->badge()->color('info')
                    ->formatStateUsing(fn (?string $state): string => DataItem::KINDS[$state] ?? '—'),
                TextColumn::make('project.title')->label('Project')->placeholder('—')->limit(30)->toggleable(),
                TextColumn::make('codes_count')->label('Codes')->counts('codes')->badge()->color('gray'),
                TextColumn::make('collected_at')->label('Collection date')->date()->placeholder('—')->sortable(),
            ])
            ->filters([
                SelectFilter::make('kind')->options(DataItem::KINDS),
                SelectFilter::make('research_project_id')->label('Project')->relationship('project', 'title'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDataItems::route('/'),
            'create' => CreateDataItem::route('/create'),
            'edit' => EditDataItem::route('/{record}/edit'),
        ];
    }
}
