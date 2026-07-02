<?php

namespace App\Filament\Resources\Documents;

use App\Filament\Resources\Documents\Pages\CreateDocument;
use App\Filament\Resources\Documents\Pages\EditDocument;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Models\Category;
use App\Models\Document;
use App\Models\Idea;
use App\Models\Keyword;
use App\Models\Topic;
use App\Services\CoThinker;
use BackedEnum;
use Filament\Actions\Action as TableAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'Knowledge Database';

    protected static ?string $modelLabel = 'Knowledge entry';

    protected static ?string $pluralModelLabel = 'Knowledge Database';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Source')->schema([
                FileUpload::make('path')
                    ->label('File')
                    ->disk('public')
                    ->directory('library')
                    ->visibility('public')
                    ->acceptedFileTypes(Document::ACCEPTED_TYPES)
                    ->maxSize(20480)
                    ->storeFileNamesIn('original_name')
                    ->downloadable()
                    ->openable()
                    ->helperText('PDF, Word, Excel, PowerPoint, CSV, text, ZIP or images — up to 20 MB')
                    ->columnSpanFull(),
                TextInput::make('title')->maxLength(200)->placeholder('Defaults to the file name')->columnSpanFull(),
                TextInput::make('subtitle')->maxLength(250)->columnSpanFull(),
                Select::make('type')->label('Type')->options(Document::TYPES)->searchable()->native(false),
                Select::make('kind')->label('Resource kind')->options(Document::KINDS)->searchable()->native(false)
                    ->placeholder('Method, framework, literature …'),
                TextInput::make('authors')->label('Author(s)')->maxLength(255),
                TextInput::make('institution')->maxLength(255),
                TextInput::make('published_by')->label('Published by')->maxLength(255),
                TextInput::make('year')->maxLength(20)->placeholder('e.g. 2024 or 2019–2021'),
                TextInput::make('pages')->maxLength(50)->placeholder('e.g. 320 or 12–45'),
                TextInput::make('website')->label('Website')->url()->prefixIcon('heroicon-m-link')->maxLength(255)->columnSpanFull(),
                Textarea::make('table_of_contents')->label('Table of content')->rows(4)->columnSpanFull(),
            ])->columns(2),

            Section::make('Alice AI — abstract & metadata')->schema([
                Actions::make([
                    Action::make('askAlice')
                        ->label('Draft abstract & suggest metadata')
                        ->icon('heroicon-m-sparkles')
                        ->color('primary')
                        ->action(function (Get $get, Set $set): void {
                            $meta = [
                                'Title' => $get('title'),
                                'Subtitle' => $get('subtitle'),
                                'Type' => Document::TYPES[$get('type')] ?? $get('type'),
                                'Authors' => $get('authors'),
                                'Institution' => $get('institution'),
                                'Published by' => $get('published_by'),
                                'Year' => $get('year'),
                                'Website' => $get('website'),
                                'Table of contents' => $get('table_of_contents'),
                                'Notes' => $get('description'),
                            ];

                            // If a PDF is attached, let Alice read its text too.
                            $pdfText = self::pdfTextFromState($get('path'));
                            if ($pdfText !== '') {
                                $meta['Document text (excerpt)'] = $pdfText;
                            }

                            try {
                                $result = app(CoThinker::class)->describeLibraryEntry($meta);
                            } catch (\Throwable $e) {
                                report($e);
                                Notification::make()->title('Alice AI is unavailable right now')->danger()->send();

                                return;
                            }

                            if ($result === []) {
                                Notification::make()->title('Alice could not suggest anything yet')
                                    ->body('Add a title and a few details, then try again.')->warning()->send();

                                return;
                            }

                            if (! empty($result['abstract'])) {
                                $set('abstract', $result['abstract']);
                            }

                            if (! empty($result['topic'])) {
                                $topic = Topic::firstOrCreate(['name' => $result['topic']]);
                                $set('topic_id', $topic->id);
                            }

                            if (! empty($result['keywords'])) {
                                $existing = $get('keyword_names') ?? [];
                                $set('keyword_names', array_values(array_unique([...$existing, ...$result['keywords']])));
                            }

                            if (! empty($result['categories'])) {
                                $ids = collect($result['categories'])
                                    ->map(fn (string $name): int => Category::firstOrCreate(['name' => $name])->id)
                                    ->all();
                                $existing = $get('categories') ?? [];
                                $set('categories', array_values(array_unique([...$existing, ...$ids])));
                            }

                            Notification::make()->title('Alice drafted an abstract and suggestions')
                                ->body('Review and edit before saving.')->success()->send();
                        }),
                ]),
                Textarea::make('abstract')
                    ->label('Abstract / Summary')
                    ->rows(6)
                    ->helperText('Drafted by Alice AI from the details above and, if a PDF is attached, its text — always review before saving.')
                    ->columnSpanFull(),
            ]),

            Section::make('Classification')->schema([
                Select::make('topic_id')->label('Topic')->relationship('topic', 'name')->searchable()->preload()
                    ->createOptionForm([TextInput::make('name')->required()->maxLength(120)]),
                Select::make('region_id')->label('Country / Region')->relationship('region', 'name')->searchable()->preload()
                    ->placeholder('Global / unspecified')
                    ->createOptionForm([TextInput::make('name')->required()->maxLength(100)]),
                Select::make('categories')->label('Categories')->relationship('categories', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Category $record): string => $record->qualifiedName())
                    ->multiple()->searchable()->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required()->maxLength(120),
                        Select::make('parent_id')->label('Parent category')->relationship('parent', 'name')
                            ->searchable()->preload()->placeholder('— top-level category —'),
                    ])
                    ->columnSpanFull(),
                TagsInput::make('keyword_names')->label('Keywords')->splitKeys([';'])
                    ->suggestions(Keyword::orderBy('name')->pluck('name')->all())
                    ->dehydrated(false)
                    ->helperText('Separate with a semicolon; pick from the suggestions or add new ones.')
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Cross-references')
                ->description('Link this entry to other work across R2N.')
                ->schema([
                    Select::make('relatedDocuments')->label('Other library entries')
                        ->relationship('relatedDocuments', 'title')->multiple()->searchable()->preload(),
                    Select::make('ideas')->label('Ideas')
                        ->relationship('ideas', 'name')->multiple()->searchable()->preload(),
                    Select::make('researchConcepts')->label('Research concepts')
                        ->relationship('researchConcepts', 'title')->multiple()->searchable()->preload(),
                    Select::make('researchProjects')->label('Research projects')
                        ->relationship('researchProjects', 'title')->multiple()->searchable()->preload(),
                ])->columns(2)->collapsible(),

            Section::make('Task')->schema([
                Textarea::make('task')->label('Task')->rows(3)
                    ->placeholder('e.g. Extract the evaluation framework and add it as a template.')
                    ->columnSpanFull(),
                Select::make('assigned_to')->label('Assigned to')->relationship('assignee', 'name')
                    ->searchable()->preload()->placeholder('Myself or a member of the network'),
            ])->columns(2)->collapsible(),

            Section::make('Notes')->schema([
                Textarea::make('description')->label('Notes')->rows(4)->columnSpanFull(),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query
                ->withCount(['relatedDocuments', 'ideas', 'researchConcepts', 'researchProjects']))
            ->columns([
                TextColumn::make('title')->weight('medium')->limit(60)->searchable()->wrap(),
                TextColumn::make('topic.name')->label('Topic')->badge()->placeholder('—')->toggleable(),
                TextColumn::make('region.name')->label('Country / Region')->badge()->color('gray')->placeholder('—')->toggleable(),
                TextColumn::make('categories.name')->label('Category')->badge()->color('success')->separator(',')->placeholder('—'),
                TextColumn::make('keywords.name')->label('Keywords')->badge()->color('gray')->separator(',')->toggleable(),
                TextColumn::make('user.name')->label('Added by')->placeholder('—')->toggleable(),
                TextColumn::make('links')
                    ->label('Link')
                    ->state(fn (Document $record): int => $record->related_documents_count
                        + $record->ideas_count + $record->research_concepts_count + $record->research_projects_count)
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'info' : 'gray')
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? $state . ' linked' : '—'),
                TextColumn::make('created_at')->since()->label('Added')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(Document::TYPES),
                SelectFilter::make('kind')->label('Resource kind')->options(Document::KINDS),
                SelectFilter::make('topic')->relationship('topic', 'name'),
                SelectFilter::make('region')->label('Country / Region')->relationship('region', 'name'),
                SelectFilter::make('keywords')->label('Keyword')->relationship('keywords', 'name')->searchable()->preload(),
            ])
            ->recordActions([
                TableAction::make('open')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Document $record): ?string => $record->url())
                    ->openUrlInNewTab()
                    ->visible(fn (Document $record): bool => filled($record->path)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Pull text from an attached PDF, whether it is a freshly uploaded temp
     * file (create/edit before save) or an already-stored path.
     */
    protected static function pdfTextFromState(mixed $path): string
    {
        $file = is_array($path) ? reset($path) : $path;

        if ($file instanceof TemporaryUploadedFile) {
            if ($file->getMimeType() === 'application/pdf') {
                return app(CoThinker::class)->extractPdfText($file->getRealPath());
            }

            return '';
        }

        if (is_string($file) && str_ends_with(strtolower($file), '.pdf')
            && Storage::disk('public')->exists($file)) {
            return app(CoThinker::class)->extractPdfText(Storage::disk('public')->path($file));
        }

        return '';
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
