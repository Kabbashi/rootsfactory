<?php

namespace App\Filament\Resources\Publications;

use App\Filament\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\Publications\Pages\CreatePublication;
use App\Filament\Resources\Publications\Pages\EditPublication;
use App\Filament\Resources\Publications\Pages\ListPublications;
use App\Filament\Resources\Publications\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\Publications\RelationManagers\VersionsRelationManager;
use App\Models\Publication;
use BackedEnum;
use Filament\Actions\Action as TableAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class PublicationResource extends Resource
{
    protected static ?string $model = Publication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Publications';

    protected static ?string $modelLabel = 'Publication';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Select::make('authors')->label('Author(s)')->relationship('authors', 'name')
                    ->multiple()->searchable()->preload()->columnSpanFull(),
                TextInput::make('title')->required()->maxLength(250)->columnSpanFull(),
                Select::make('type')->options(Publication::TYPES)->default('working_paper')->required(),
                Select::make('status')->label('Editorial stage')->options(Publication::STATUSES)->default('draft')->required()
                    ->helperText('When set to “Published”, it appears in the public list with a publication date.'),
                Select::make('topic_id')->label('Topic')->relationship('topic', 'name')->searchable()->preload()
                    ->createOptionForm([TextInput::make('name')->required()->maxLength(120)]),
                Select::make('region_id')->label('Country / Region')->relationship('region', 'name')->searchable()->preload()
                    ->createOptionForm([TextInput::make('name')->required()->maxLength(100)]),
                Select::make('research_project_id')->label('Research project')->relationship('project', 'title')->searchable()->preload(),
                TextInput::make('language')->default('en')->maxLength(8),
                TextInput::make('doi')->label('DOI / identifier')->maxLength(200),
                TagsInput::make('published_in')->label('Published at (portals / websites)')
                    ->default(['R2N (Rootsfactory Research Network)'])
                    ->helperText('Where this was published. Defaults to R2N; add others as needed.')
                    ->columnSpanFull(),
                Textarea::make('abstract')->rows(4)->columnSpanFull(),
                Textarea::make('citation')->label('Bibliographical Reference')->rows(2)->columnSpanFull(),
            ])->columns(2),
            Section::make('Document')->schema([
                FileUpload::make('path')->label('Document')
                    ->disk('public')->directory('publications')->visibility('public')
                    ->acceptedFileTypes(['application/pdf', 'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->maxSize(30720)->storeFileNamesIn('original_name')->downloadable()->openable()
                    ->helperText('PDF or Word — readers can open and download it.')
                    ->columnSpanFull(),
                Textarea::make('body')->label('Table of content')->rows(12)->columnSpanFull(),
            ])->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['authors', 'topic', 'region']))
            ->groups([
                Group::make('region.name')->label('Country / Region'),
                Group::make('topic.name')->label('Topic'),
            ])
            ->columns([
                TextColumn::make('authors')->label('Author')
                    ->state(fn (Publication $record): string => $record->authorLine())
                    ->wrap()->limit(40),
                TextColumn::make('topic.name')->label('Topic')->badge()->placeholder('—')->toggleable(),
                TextColumn::make('title')->weight('medium')->wrap()->searchable()->limit(60),
                TextColumn::make('region.name')->label('Country / Region')->badge()->color('gray')->placeholder('—'),
                TextColumn::make('published_at')->label('Published on')->date()->placeholder('—')->sortable(),
                TextColumn::make('published_in')->label('Published at')->badge()->color('info')->placeholder('—')->toggleable(),
                TextColumn::make('type')->badge()->color('info')
                    ->formatStateUsing(fn (?string $state): string => Publication::TYPES[$state] ?? '—'),
            ])
            ->filters([
                SelectFilter::make('type')->options(Publication::TYPES),
                SelectFilter::make('status')->label('Editorial stage')->options(Publication::STATUSES),
                SelectFilter::make('topic')->relationship('topic', 'name'),
                SelectFilter::make('region')->label('Country / Region')->relationship('region', 'name'),
            ])
            ->recordActions([
                TableAction::make('download')
                    ->label('Download')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->url(fn (Publication $record): ?string => $record->url())
                    ->openUrlInNewTab()
                    ->visible(fn (Publication $record): bool => filled($record->path)),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            VersionsRelationManager::class,
            ReviewsRelationManager::class,
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublications::route('/'),
            'create' => CreatePublication::route('/create'),
            'edit' => EditPublication::route('/{record}/edit'),
        ];
    }
}
