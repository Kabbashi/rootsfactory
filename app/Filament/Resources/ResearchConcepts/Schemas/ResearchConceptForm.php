<?php

namespace App\Filament\Resources\ResearchConcepts\Schemas;

use App\Models\Category;
use App\Models\Keyword;
use App\Models\ResearchConcept;
use App\Models\Topic;
use App\Services\CoThinker;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ResearchConceptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(200)
                    ->columnSpanFull(),
                Select::make('topic_id')
                    ->label('Topic')
                    ->relationship('topic', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                    ]),
                Select::make('region_id')
                    ->label('Region')
                    ->relationship('region', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Global / unspecified')
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                    ]),
                Select::make('type')
                    ->label('Type')
                    ->options(ResearchConcept::TYPES)
                    ->default('brief')
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options(ResearchConcept::STATUS_LABELS)
                    ->default('draft')
                    ->required()
                    ->helperText('Once Final, the concept is locked — only the person who brought it in can change it.'),
                Toggle::make('pinned')
                    ->label('Pinned')
                    ->helperText('Keep at the top of the feed'),
                Select::make('categories')
                    ->label('Categories')
                    ->relationship('categories', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Category $record): string => $record->qualifiedName())
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required()->maxLength(120),
                        Select::make('parent_id')
                            ->label('Parent category')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('— top-level category —'),
                    ]),
                TagsInput::make('keyword_names')
                    ->label('Keywords')
                    ->splitKeys([';'])
                    ->suggestions(Keyword::orderBy('name')->pluck('name')->all())
                    ->dehydrated(false)
                    ->helperText('Separate keywords with a semicolon.'),
                Placeholder::make('contributors')
                    ->label('Contributors')
                    ->content(fn (?ResearchConcept $record): string => $record
                        ? implode(', ', $record->contributorNames())
                        : '—')
                    ->visible(fn (?ResearchConcept $record): bool => (bool) $record?->isFromPublicIdea())
                    ->columnSpanFull(),
                FileUpload::make('alice_pdf')
                    ->label('Alice: analyse a PDF (optional)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(20480)
                    ->dehydrated(false)
                    ->helperText('Attach a PDF for Alice to read, then use “Research & expand with Alice”.')
                    ->columnSpanFull(),
                Actions::make([
                    Action::make('askAlice')
                        ->label('Research & expand with Alice')
                        ->icon('heroicon-m-sparkles')
                        ->color('primary')
                        ->action(function (Get $get, Set $set): void {
                            $topic = $get('topic_id') ? Topic::find($get('topic_id'))?->name : null;
                            $context = "Research concept title: " . ($get('title') ?: '(untitled)') . "\n"
                                . ($topic ? "Topic: {$topic}\n" : '')
                                . "Current content:\n" . ($get('body') ?: '(empty)');

                            try {
                                $pdf = app(CoThinker::class)->pdfTextFromUpload($get('alice_pdf'));
                                $draft = app(CoThinker::class)->assist(
                                    'You are Alice, helping shape a research concept. Research the topic and expand '
                                    . 'the concept into a fuller, well-structured draft (problem, proposal, approach, '
                                    . 'open questions). Keep it a proposal, not a claim of fact.',
                                    $context,
                                    $pdf,
                                );
                            } catch (\Throwable $e) {
                                report($e);
                                Notification::make()->title('Alice is unavailable right now')->danger()->send();

                                return;
                            }

                            $existing = trim((string) $get('body'));
                            $set('body', $existing === '' ? $draft : $existing . "\n\n---\n\n" . $draft);

                            Notification::make()->title('Alice added a draft to the content')
                                ->body('Review and edit before saving.')->success()->send();
                        }),
                ])->columnSpanFull(),
                MarkdownEditor::make('body')
                    ->label('Content')
                    ->columnSpanFull(),
            ])
            // A final concept is read-only for everyone but its originator.
            ->disabled(fn (?ResearchConcept $record): bool => (bool) $record?->isLockedFor(auth()->id()));
    }
}
