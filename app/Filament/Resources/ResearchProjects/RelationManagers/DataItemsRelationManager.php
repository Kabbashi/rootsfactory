<?php

namespace App\Filament\Resources\ResearchProjects\RelationManagers;

use App\Models\DataItem;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DataItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'dataItems';

    protected static ?string $title = 'Data items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(200)->columnSpanFull(),
            Select::make('kind')->options(DataItem::KINDS)->default('transcript')->required(),
            DatePicker::make('collected_at')->label('Collected')->native(false),
            Textarea::make('content')->rows(10)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('collected_at', 'desc')
            ->columns([
                TextColumn::make('title')->weight('medium')->wrap()->searchable(),
                TextColumn::make('kind')->badge()->color('info')
                    ->formatStateUsing(fn (?string $state): string => DataItem::KINDS[$state] ?? '—'),
                TextColumn::make('collected_at')->date()->placeholder('—')->sortable(),
            ])
            ->filters([
                SelectFilter::make('kind')->options(DataItem::KINDS),
            ])
            ->headerActions([
                CreateAction::make()->mutateDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();

                    return $data;
                }),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
