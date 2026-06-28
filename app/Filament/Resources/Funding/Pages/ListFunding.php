<?php

namespace App\Filament\Resources\Funding\Pages;

use App\Filament\Resources\Funding\FundingResource;
use App\Services\CoThinker;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListFunding extends ListRecords
{
    protected static string $resource = FundingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ai_suggest')
                ->label('Suggest leads (AI)')
                ->icon('heroicon-m-sparkles')
                ->color('gray')
                ->modalSubmitActionLabel('Suggest')
                ->modalDescription('Roots Factory AI proposes types of funders and programmes worth '
                    . 'researching, based on the team\'s themes. These are leads to verify — not real '
                    . 'calls, deadlines or amounts.')
                ->form([
                    Textarea::make('focus')
                        ->label('Focus (optional)')
                        ->placeholder('e.g. climate adaptation in the Sahel, youth employment…')
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    try {
                        $text = app(CoThinker::class)->suggestFunding($data['focus'] ?? null);
                    } catch (\Throwable $e) {
                        report($e);
                        Notification::make()
                            ->title('AI is unavailable right now')
                            ->body('Could not reach the AI gateway. Please try again later.')
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('AI funding leads — verify before use')
                        ->body(Str::markdown($text))
                        ->success()
                        ->persistent()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
