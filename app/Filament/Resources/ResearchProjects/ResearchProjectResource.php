<?php

namespace App\Filament\Resources\ResearchProjects;

use App\Filament\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\ResearchProjects\Pages\CreateResearchProject;
use App\Filament\Resources\ResearchProjects\Pages\EditResearchProject;
use App\Filament\Resources\ResearchProjects\Pages\ListResearchProjects;
use App\Filament\Resources\ResearchProjects\RelationManagers\DataItemsRelationManager;
use App\Filament\Resources\ResearchProjects\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\ResearchProjects\RelationManagers\EvidenceRelationManager;
use App\Filament\Resources\ResearchProjects\RelationManagers\ReferencesRelationManager;
use App\Filament\RelationManagers\TasksRelationManager;
use App\Filament\Resources\ResearchProjects\RelationManagers\TeamRelationManager;
use App\Models\Category;
use App\Models\ResearchProject;
use App\Services\CoThinker;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ResearchProjectResource extends Resource
{
    protected static ?string $model = ResearchProject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string|\UnitEnum|null $navigationGroup = 'Research Hub';

    protected static ?string $modelLabel = 'Research project';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Overview')->schema([
                TextInput::make('title')->required()->maxLength(200)->columnSpanFull(),
                Select::make('kind')->options(ResearchProject::KINDS)->default('project')->required(),
                Select::make('status')->options(ResearchProject::STATUSES)->default('planned')->required(),
                Select::make('lead_user_id')->label('Lead')->relationship('lead', 'name')->searchable()->preload(),
                Select::make('topics')->relationship('topics', 'name')->multiple()->searchable()->preload(),
                Select::make('regions')->label('Countries / regions')->relationship('regions', 'name')->multiple()->searchable()->preload(),
                Select::make('categories')
                    ->label('Categories')
                    ->relationship('categories', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Category $record): string => $record->qualifiedName())
                    ->multiple()->searchable()->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required()->maxLength(120),
                        Select::make('parent_id')->label('Parent category')->relationship('parent', 'name')
                            ->searchable()->preload()->placeholder('— top-level category —'),
                    ])
                    ->columnSpanFull(),
                DatePicker::make('start_date')->native(false),
                DatePicker::make('end_date')->native(false),
                Textarea::make('summary')->rows(3)->columnSpanFull(),
            ])->columns(2),
            Section::make('Research design')->schema([
                Textarea::make('objectives')->rows(3)->columnSpanFull(),
                Textarea::make('research_questions')->rows(3)->columnSpanFull(),
                Textarea::make('methodology')->rows(3)->columnSpanFull(),
                Textarea::make('data_collection')->label('Data collection')->rows(3)->columnSpanFull(),
                Textarea::make('findings')->rows(3)->columnSpanFull(),
            ])->collapsible(),
            Section::make('Alice AI')->schema([
                FileUpload::make('alice_pdf')
                    ->label('Analyse a PDF (optional)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(20480)
                    ->dehydrated(false)
                    ->helperText('Attach a document for Alice to read, then draft findings from it.')
                    ->columnSpanFull(),
                Actions::make([
                    Action::make('askAlice')
                        ->label('Analyse & draft findings with Alice')
                        ->icon('heroicon-m-sparkles')
                        ->color('primary')
                        ->action(function (Get $get, Set $set): void {
                            $context = "Research project: " . ($get('title') ?: '(untitled)') . "\n"
                                . ($get('summary') ? "Summary: {$get('summary')}\n" : '')
                                . ($get('objectives') ? "Objectives: {$get('objectives')}\n" : '')
                                . ($get('methodology') ? "Methodology: {$get('methodology')}\n" : '')
                                . "Existing findings:\n" . ($get('findings') ?: '(none yet)');

                            try {
                                $pdf = app(CoThinker::class)->pdfTextFromUpload($get('alice_pdf'));
                                $draft = app(CoThinker::class)->assist(
                                    'You are Alice, supporting a research project. Analyse the attached document and '
                                    . 'the project context, then draft concise, structured findings and key points '
                                    . 'the team can refine. Attribute claims to the document; do not overstate.',
                                    $context,
                                    $pdf,
                                );
                            } catch (\Throwable $e) {
                                report($e);
                                Notification::make()->title('Alice is unavailable right now')->danger()->send();

                                return;
                            }

                            $existing = trim((string) $get('findings'));
                            $set('findings', $existing === '' ? $draft : $existing . "\n\n---\n\n" . $draft);

                            Notification::make()->title('Alice drafted findings')
                                ->body('Review and edit before saving.')->success()->send();
                        }),
                ])->columnSpanFull(),
            ])->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')->weight('medium')->wrap()->searchable()->limit(60),
                TextColumn::make('kind')->badge()->color('info')
                    ->formatStateUsing(fn (?string $state): string => ResearchProject::KINDS[$state] ?? '—'),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (?string $state): string => ResearchProject::STATUSES[$state] ?? '—')
                    ->color(fn (?string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'info',
                        'published' => 'success',
                        'archived' => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('lead.name')->label('Lead')->placeholder('—')->toggleable(),
                TextColumn::make('start_date')->date()->placeholder('—')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ResearchProject::STATUSES),
                SelectFilter::make('kind')->options(ResearchProject::KINDS),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            TeamRelationManager::class,
            TasksRelationManager::class,
            DocumentsRelationManager::class,
            DataItemsRelationManager::class,
            EvidenceRelationManager::class,
            ReferencesRelationManager::class,
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResearchProjects::route('/'),
            'create' => CreateResearchProject::route('/create'),
            'edit' => EditResearchProject::route('/{record}/edit'),
        ];
    }
}
