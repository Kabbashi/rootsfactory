<?php

namespace App\Filament\Resources\DataItems\RelationManagers;

use App\Models\Category;
use App\Models\Code;
use App\Models\Coding;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Code excerpts of this data item — the coding workflow. Each row is one
 * piece of evidence: a code applied to a passage, by a coder.
 */
class CodingsRelationManager extends RelationManager
{
    protected static string $relationship = 'codings';

    protected static ?string $title = 'Coding';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('code_id')
                ->label('Code')
                ->relationship('code', 'name')
                ->getOptionLabelFromRecordUsing(fn (Code $record): string => $record->name
                    . ($record->category ? ' · ' . $record->category->qualifiedName() : ''))
                ->searchable()
                ->preload()
                ->required()
                ->createOptionForm([
                    TextInput::make('name')->required()->maxLength(150),
                    Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->getOptionLabelFromRecordUsing(fn (Category $record): string => $record->qualifiedName())
                        ->searchable()
                        ->preload(),
                    ColorPicker::make('color'),
                ]),
            Textarea::make('excerpt')
                ->label('Excerpt')
                ->helperText('The passage this code applies to.')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code.name')
                    ->label('Code')
                    ->badge()
                    ->color(fn (Coding $record): string => $record->code?->color ? 'gray' : 'primary'),
                TextColumn::make('excerpt')->wrap()->limit(160)->placeholder('—'),
                TextColumn::make('user.name')->label('Coder')->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->since()->label('When')->toggleable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add coding')
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
