<?php

namespace App\Jobs;

use App\Models\Idea;
use App\Models\User;
use App\Services\CoThinker;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Runs one co-thinker pass on an idea and posts the result as a comment by
 * the AI author, so the team sees it inside the discussion thread.
 */
class GenerateAiInsight implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 180;

    public function __construct(
        public int $ideaId,
        public string $mode,
    ) {}

    public function handle(CoThinker $ai): void
    {
        $idea = Idea::find($this->ideaId);

        if (! $idea) {
            return;
        }

        [$heading, $text] = match ($this->mode) {
            'summarize' => ['🤖 Summary', $ai->summarize($idea)],
            'red_team' => ['🤖 Red-team — challenges & blind spots', $ai->redTeam($idea)],
            'related' => ['🤖 Related ideas', $ai->relatedIdeas($idea)],
            default => throw new \InvalidArgumentException("Unknown co-thinker mode: {$this->mode}"),
        };

        $idea->comments()->create([
            'user_id' => User::aiAuthor()->id,
            'body' => "**{$heading}**\n\n{$text}",
        ]);
    }
}
