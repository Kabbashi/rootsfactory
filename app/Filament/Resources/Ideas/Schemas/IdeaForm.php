<?php

namespace App\Filament\Resources\Ideas\Schemas;

use App\Models\Category;
use App\Models\Idea;
use App\Models\Keyword;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class IdeaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->helperText('A short name — one or two words.')
                    ->required()
                    ->maxLength(120)
                    ->columnSpanFull(),
                Textarea::make('core_statement')
                    ->label('Core statement')
                    ->helperText('The heart of the idea, in a sentence or two.')
                    ->rows(2)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Description')
                    ->rows(5)
                    ->columnSpanFull(),
                FileUpload::make('image_path')
                    ->label('Image (for AI)')
                    ->image()
                    ->disk('public')
                    ->directory('idea-images')
                    ->imagePreviewHeight('120')
                    ->helperText('Optional. A sketch/photo the AI can read — save the idea, then use "Suggest core statement from image".')
                    ->columnSpanFull(),
                FileUpload::make('attachments')
                    ->label('Attachments')
                    ->multiple()
                    ->disk('public')
                    ->directory('idea-files')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/plain',
                        'image/png',
                        'image/jpeg',
                    ])
                    ->maxSize(20480)
                    ->downloadable()
                    ->openable()
                    ->reorderable()
                    ->helperText('Documents to support the idea — PDF, Word, Excel, text or images (max 20 MB each).')
                    ->columnSpanFull(),
                Radio::make('visibility')
                    ->options(Idea::VISIBILITIES)
                    ->default('personal')
                    ->required(),
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
                    ->helperText('Separate keywords with a semicolon; pick from the suggestions or add new ones.')
                    ->columnSpanFull(),
                Select::make('crossReferences')
                    ->label('Related ideas')
                    ->relationship(
                        'crossReferences',
                        'name',
                        fn (Builder $query, ?Idea $record) => $query
                            ->visibleTo(auth()->id())
                            ->when($record, fn (Builder $q) => $q->whereKeyNot($record->getKey())),
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('Link to other ideas — these become the connections in the idea map.')
                    ->columnSpanFull(),
            ]);
    }
}
