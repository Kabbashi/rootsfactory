<?php

namespace App\Jobs;

use App\Models\ResearchConcept;
use App\Models\Topic;
use App\Models\User;
use App\Services\CoThinker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Runs one co-thinker pass on an idea or topic and posts the result as a
 * comment by the AI author, so the team sees it inside the discussion thread.
 */
class GenerateAiInsight implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 180;

    /**
     * @param  'idea'|'topic'  $subjectType
     * @param  'summarize'|'red_team'|'related'  $mode
     */
    public function __construct(
        public string $subjectType,
        public int $subjectId,
        public string $mode,
    ) {}

    /**
     * Dispatch a pass for any supported model (idea or topic).
     */
    public static function for(Model $subject, string $mode): void
    {
        $type = match (true) {
            $subject instanceof ResearchConcept => 'idea',
            $subject instanceof Topic => 'topic',
            default => throw new \InvalidArgumentException('Unsupported co-thinker subject: ' . $subject::class),
        };

        static::dispatch($type, $subject->getKey(), $mode);
    }

    public function handle(CoThinker $ai): void
    {
        $subject = match ($this->subjectType) {
            'idea' => ResearchConcept::find($this->subjectId),
            'topic' => Topic::find($this->subjectId),
            default => null,
        };

        if (! $subject) {
            return;
        }

        [$heading, $text] = $this->generate($ai, $subject);

        $subject->comments()->create([
            'user_id' => User::aiAuthor()->id,
            'body' => "**{$heading}**\n\n{$text}",
        ]);
    }

    /**
     * @return array{0: string, 1: string}  [heading, body]
     */
    private function generate(CoThinker $ai, ResearchConcept|Topic $subject): array
    {
        if ($subject instanceof ResearchConcept) {
            return match ($this->mode) {
                'summarize' => ['🤖 Summary', $ai->summarize($subject)],
                'red_team' => ['🤖 Red-team — challenges & blind spots', $ai->redTeam($subject)],
                'related' => ['🤖 Related ideas', $ai->relatedIdeas($subject)],
                default => throw new \InvalidArgumentException("Unknown co-thinker mode: {$this->mode}"),
            };
        }

        return match ($this->mode) {
            'summarize' => ['🤖 Synthesis', $ai->summarizeTopic($subject)],
            'red_team' => ['🤖 Red-team — challenges & gaps', $ai->redTeamTopic($subject)],
            'related' => ['🤖 Related topics', $ai->relatedTopics($subject)],
            default => throw new \InvalidArgumentException("Unknown co-thinker mode: {$this->mode}"),
        };
    }
}
