<?php

namespace App\Filament\Resources\Publications;

use App\Filament\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\Publications\Pages\CreatePublication;
use App\Filament\Resources\Publications\Pages\EditPublication;
use App\Filament\Resources\Publications\Pages\ListPublications;
use App\Filament\Resources\Publications\RelationManagers\AuthorsRelationManager;
use App\Filament\Resources\Publications\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\Publications\RelationManagers\VersionsRelationManager;
use App\Models\Publication;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                TextInput::make('title')->required()->maxLength(250)->columnSpanFull(),
                Select::make('type')->options(Publication::TYPES)->default('working_paper')->required(),
                Select::make('status')->label('Editorial stage')->options(Publication::STATUSES)->default('draft')->required(),
                Select::make('research_project_id')->label('Research project')->relationship('project', 'title')->searchable()->preload(),
                TextInput::make('language')->default('en')->maxLength(8),
                TextInput::make('doi')->label('DOI / identifier')->maxLength(200),
                Textarea::make('abstract')->rows(4)->columnSpanFull(),
                Textarea::make('citation')->rows(2)->columnSpanFull(),
            ])->columns(2),
            Section::make('Manuscript')->schema([
                Textarea::make('body')->label('Body (Markdown)')->rows(18)->columnSpanFull(),
            ])->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')->weight('medium')->wrap()->searchable()->limit(70),
                TextColumn::make('type')->badge()->color('info')
                    ->formatStateUsing(fn (?string $state): string => Publication::TYPES[$state] ?? '—'),
                TextColumn::make('status')->label('Stage')->badge()
                    ->formatStateUsing(fn (?string $state): string => Publication::STATUSES[$state] ?? '—')
                    ->color(fn (?string $state): string => match ($state) {
                        'published' => 'success',
                        'approved' => 'info',
                        'archived' => 'gray',
                        'draft' => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('published_at')->date()->placeholder('—')->sortable()->toggleable(),
                TextColumn::make('downloads')->numeric()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Editorial stage')->options(Publication::STATUSES),
                SelectFilter::make('type')->options(Publication::TYPES),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            AuthorsRelationManager::class,
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
