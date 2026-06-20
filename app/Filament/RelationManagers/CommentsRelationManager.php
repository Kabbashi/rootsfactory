<?php

namespace App\Filament\RelationManagers;

use App\Models\Comment;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Shared discussion thread. Attached to any resource whose model has a
 * polymorphic `comments` relation (ideas, topics, …). Supports threaded
 * replies via each comment's `parent_id`.
 */
class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Discussion';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('body')
                    ->label('Comment')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->defaultSort('created_at', 'asc')
            ->modifyQueryUsing(fn ($query) => $query->with('parent.user'))
            ->columns([
                TextColumn::make('user.name')
                    ->label('By')
                    ->weight('medium'),
                TextColumn::make('body')
                    ->label('Comment')
                    ->wrap()
                    ->description(
                        fn (Comment $record): ?string => $record->parent_id
                            ? '↳ in reply to ' . ($record->parent?->user?->name ?? 'a comment')
                            : null,
                        position: 'above',
                    ),
                TextColumn::make('created_at')
                    ->label('When')
                    ->since(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add comment')
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->schema([
                        Textarea::make('body')
                            ->label('Reply')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (array $data, Comment $record): void {
                        $this->getOwnerRecord()->comments()->create([
                            'user_id' => auth()->id(),
                            'parent_id' => $record->id,
                            'body' => $data['body'],
                        ]);
                    }),
                EditAction::make()
                    ->visible(fn (Comment $record): bool => $record->user_id === auth()->id()),
                DeleteAction::make()
                    ->visible(fn (Comment $record): bool => $record->user_id === auth()->id()),
            ]);
    }
}
