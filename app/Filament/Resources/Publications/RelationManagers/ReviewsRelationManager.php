<?php

namespace App\Filament\Resources\Publications\RelationManagers;

use App\Models\Review;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'Reviews';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('reviewer_id')->label('Reviewer')->relationship('reviewer', 'name')->searchable()->preload(),
            Select::make('stage')->options(Review::STAGES)->default('internal')->required(),
            Select::make('status')->options(Review::STATUSES)->default('pending')->required(),
            Select::make('recommendation')->options(Review::RECOMMENDATIONS),
            DateTimePicker::make('due_at')->label('Due')->native(false),
            Textarea::make('comments')->rows(4)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reviewer.name')->label('Reviewer')->placeholder('Unassigned'),
                TextColumn::make('stage')->badge()
                    ->formatStateUsing(fn (?string $state): string => Review::STAGES[$state] ?? $state),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (?string $state): string => Review::STATUSES[$state] ?? $state)
                    ->color(fn (?string $state): string => match ($state) {
                        'done' => 'success',
                        'in_progress' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('recommendation')->badge()->placeholder('—')
                    ->formatStateUsing(fn (?string $state): string => Review::RECOMMENDATIONS[$state] ?? '—')
                    ->color(fn (?string $state): string => match ($state) {
                        'accept' => 'success',
                        'reject' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('due_at')->dateTime()->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('stage')->options(Review::STAGES),
                SelectFilter::make('status')->options(Review::STATUSES),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
