<?php

namespace App\Filament\Resources\Documents;

use App\Filament\Resources\Documents\Pages\CreateDocument;
use App\Filament\Resources\Documents\Pages\EditDocument;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Models\Document;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Knowledge Center';

    protected static ?string $modelLabel = 'Document';

    protected static ?string $pluralModelLabel = 'Library';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('path')
                ->label('File')
                ->required()
                ->disk('public')
                ->directory('library')
                ->visibility('public')
                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                ->maxSize(20480) // 20 MB
                ->storeFileNamesIn('original_name')
                ->downloadable()
                ->openable()
                ->helperText('PDF, JPG or PNG, up to 20 MB')
                ->columnSpanFull(),
            TextInput::make('title')
                ->maxLength(200)
                ->placeholder('Defaults to the file name')
                ->columnSpanFull(),
            Select::make('topic_id')
                ->label('Topic')
                ->relationship('topic', 'name')
                ->searchable()
                ->preload(),
            Select::make('region_id')
                ->label('Region')
                ->relationship('region', 'name')
                ->searchable()
                ->preload()
                ->placeholder('Global / unspecified'),
            Textarea::make('description')
                ->rows(4)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->weight('medium')
                    ->limit(60)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('mime')
                    ->label('Type')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'application/pdf' => 'PDF',
                        'image/jpeg' => 'JPG',
                        'image/png' => 'PNG',
                        default => $state ?? '—',
                    }),
                TextColumn::make('size')
                    ->formatStateUsing(fn (?Document $record): string => $record?->sizeForHumans() ?? '—')
                    ->toggleable(),
                TextColumn::make('topic.name')
                    ->label('Topic')
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('region.name')
                    ->label('Region')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('Added by')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Added')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('mime')
                    ->label('Type')
                    ->options([
                        'application/pdf' => 'PDF',
                        'image/jpeg' => 'JPG',
                        'image/png' => 'PNG',
                    ]),
                SelectFilter::make('topic')
                    ->relationship('topic', 'name'),
                SelectFilter::make('region')
                    ->relationship('region', 'name'),
            ])
            ->recordActions([
                Action::make('open')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Document $record): ?string => $record->url())
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }
}
