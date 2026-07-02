<?php

namespace App\Services;

use App\Models\ResearchConcept;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\Http;

/**
 * Roots Factory's AI co-thinker.
 *
 * Talks to the shared LiteLLM gateway (OpenAI-compatible) using a logical
 * role alias — no SDK, just HTTP, so there is no extra composer dependency.
 * It never decides which provider/model runs; that lives in litellm/config.yaml.
 */
class CoThinker
{
    private const SYSTEM = <<<'TXT'
        You are Roots Factory's AI co-thinker, supporting a development-cooperation (EZ) think tank.
        Your job is to help the team sharpen ideas — not to decide for them.
        Be concise, concrete and intellectually honest. Write in English, in Markdown.
        Never invent facts, numbers or citations; if you are unsure, say so plainly.
        TXT;

    /**
     * One-line brief of the idea and its discussion.
     */
    public function summarize(ResearchConcept $idea): string
    {
        return $this->chat([
            ['role' => 'system', 'content' => self::SYSTEM],
            ['role' => 'user', 'content' =>
                "Summarise the idea and its discussion below into a crisp brief: the core proposition, "
                . "the key points raised, and any open questions. Use short paragraphs or bullets, 120 words max.\n\n"
                . $this->ideaContext($idea),
            ],
        ]);
    }

    /**
     * Expand a short idea into a fuller, structured brief — without inventing
     * any facts, figures, places or citations. Returns Markdown for the body.
     */
    public function expand(ResearchConcept $idea): string
    {
        return $this->chat([
            ['role' => 'system', 'content' => self::SYSTEM],
            ['role' => 'user', 'content' =>
                "Expand the idea below into a fuller policy brief of roughly 300–450 words, "
                . "drawing on the idea and its discussion. Structure it with these Markdown H2 headings:\n"
                . "## The problem\n## The proposal\n## How it works\n## Who is involved\n## Open questions\n\n"
                . "Hard rules: do NOT invent statistics, monetary figures, dates, named organisations, "
                . "specific places or citations that are not already in the material. Keep it a proposal "
                . "(\"could\", \"would\", \"proposes\"), not a claim of fact. Stay faithful to the original intent.\n\n"
                . $this->ideaContext($idea),
            ],
        ], maxTokens: 900);
    }

    /**
     * Constructive red team: challenges, risks, blind spots.
     */
    public function redTeam(ResearchConcept $idea): string
    {
        return $this->chat([
            ['role' => 'system', 'content' => self::SYSTEM],
            ['role' => 'user', 'content' =>
                "Act as a constructive red team for the idea below. Identify the 3–5 strongest challenges, "
                . "risks, blind spots or counterarguments — one sentence each, as a bullet list. "
                . "Then end with a line starting \"**Next question:**\" naming the single most important "
                . "question the team should resolve next.\n\n"
                . $this->ideaContext($idea),
            ],
        ]);
    }

    /**
     * Surface related ideas elsewhere in the workspace.
     */
    public function relatedIdeas(ResearchConcept $idea): string
    {
        $others = ResearchConcept::query()
            ->where('id', '!=', $idea->id)
            ->with('topic')
            ->latest()
            ->limit(50)
            ->get();

        if ($others->isEmpty()) {
            return 'There are no other ideas in the workspace yet to relate this one to.';
        }

        $list = $others
            ->map(fn (ResearchConcept $o): string => "#{$o->id} [{$o->topic?->name}] {$o->title}")
            ->implode("\n");

        return $this->chat([
            ['role' => 'system', 'content' => self::SYSTEM],
            ['role' => 'user', 'content' =>
                "Here is the current idea, followed by other ideas in the workspace.\n\n"
                . "CURRENT IDEA:\n" . $this->ideaContext($idea, withDiscussion: false) . "\n\n"
                . "OTHER IDEAS (id, [topic], title):\n{$list}\n\n"
                . "List the ones most genuinely related to the current idea, each as "
                . "\"#id — one line on how they connect\". If none are clearly related, say so honestly. "
                . "Do not invent ideas that are not in the list.",
            ],
        ]);
    }

    /**
     * Synthesise all ideas gathered under a topic.
     */
    public function summarizeTopic(Topic $topic): string
    {
        return $this->chat([
            ['role' => 'system', 'content' => self::SYSTEM],
            ['role' => 'user', 'content' =>
                "Synthesise the ideas gathered under this topic into: the main themes, "
                . "the points of agreement, the open tensions, and the notable gaps worth exploring. "
                . "Use short headed bullets, 150 words max.\n\n"
                . $this->topicContext($topic),
            ],
        ]);
    }

    /**
     * Red team a whole topic: cross-cutting risks and gaps.
     */
    public function redTeamTopic(Topic $topic): string
    {
        return $this->chat([
            ['role' => 'system', 'content' => self::SYSTEM],
            ['role' => 'user', 'content' =>
                "Act as a constructive red team for this topic as a whole. Across all the ideas below, "
                . "identify the 3–5 biggest cross-cutting risks, blind spots or missing perspectives — "
                . "one sentence each, as a bullet list. Then end with a line starting \"**Next question:**\" "
                . "naming the single most important question the team should resolve for this topic.\n\n"
                . $this->topicContext($topic),
            ],
        ]);
    }

    /**
     * Surface related topics elsewhere in the workspace.
     */
    public function relatedTopics(Topic $topic): string
    {
        $others = Topic::query()
            ->where('id', '!=', $topic->id)
            ->withCount('researchConcepts')
            ->get();

        if ($others->isEmpty()) {
            return 'There are no other topics in the workspace yet to relate this one to.';
        }

        $list = $others
            ->map(fn (Topic $o): string => "#{$o->id} ({$o->research_concepts_count} ideas) {$o->name}"
                . ($o->description ? ' — ' . (string) str($o->description)->limit(80) : ''))
            ->implode("\n");

        return $this->chat([
            ['role' => 'system', 'content' => self::SYSTEM],
            ['role' => 'user', 'content' =>
                "Here is the current topic, followed by other topics in the workspace.\n\n"
                . "CURRENT TOPIC:\n{$topic->name}" . ($topic->description ? " — {$topic->description}" : '') . "\n\n"
                . "OTHER TOPICS (id, idea count, name — description):\n{$list}\n\n"
                . "List the ones most genuinely related to the current topic, each as "
                . "\"#id — one line on how they connect\". If none are clearly related, say so honestly. "
                . "Do not invent topics that are not in the list.",
            ],
        ]);
    }

    /**
     * Answer a public question grounded ONLY in the given published sources,
     * citing them inline as [1], [2]… No outside knowledge, no invented facts.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\ResearchConcept>  $sources
     */
    public function answerQuestion(string $question, $sources): string
    {
        $context = $sources->values()
            ->map(fn ($source, int $i): string => '[' . ($i + 1) . '] ' . $source->title . "\n"
                . (string) str(strip_tags(\Illuminate\Support\Str::markdown(
                    $source->abstract ?? $source->body ?? ''
                )))->limit(1200))
            ->implode("\n\n");

        return $this->chat([
            ['role' => 'system', 'content' => self::SYSTEM],
            ['role' => 'user', 'content' =>
                "Answer the question using ONLY the numbered sources below — they are Roots Factory's "
                . "own published briefs. Cite the sources you draw on inline as [1], [2], matching their "
                . "numbers. If the sources do not contain enough to answer, say so plainly and do not guess. "
                . "Use no knowledge beyond these sources, and never invent facts, figures or citations. "
                . "Be concise (under 200 words) and write in Markdown.\n\n"
                . "QUESTION:\n{$question}\n\n"
                . "SOURCES:\n{$context}",
            ],
        ]);
    }

    /**
     * Free-form thinking partner for the Agent Center. Helps the team explore
     * a question or draft something — honest about uncertainty, no invented
     * facts. The workspace's topics are offered as light context.
     */
    public function brainstorm(string $prompt): string
    {
        $topics = Topic::query()->pluck('name')->implode(', ');

        return $this->chat([
            ['role' => 'system', 'content' => self::SYSTEM],
            ['role' => 'user', 'content' =>
                ($topics !== '' ? "For context, the think tank currently works on these topics: {$topics}.\n\n" : '')
                . $prompt,
            ],
        ], maxTokens: 900);
    }

    /**
     * Low-level chat completion against the LiteLLM gateway.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    /**
     * Vision: look at an uploaded image (sketch, photo, diagram, hand note)
     * and propose a concise "core statement" for the research idea it suggests.
     * Uses the same gateway; the configured model must be vision-capable.
     */
    public function coreStatementFromImage(string $absolutePath): string
    {
        $data = @file_get_contents($absolutePath);
        if ($data === false) {
            throw new \RuntimeException('Could not read the image file.');
        }

        $mime = function_exists('mime_content_type')
            ? (mime_content_type($absolutePath) ?: 'image/jpeg')
            : 'image/jpeg';
        $dataUri = 'data:' . $mime . ';base64,' . base64_encode($data);

        return $this->chat([
            ['role' => 'system', 'content' => self::SYSTEM],
            ['role' => 'user', 'content' => [
                ['type' => 'text', 'text' =>
                    'This image is a sketch, photo, diagram or note behind a research idea. '
                    . 'In one or two sentences, propose a clear "core statement" capturing the '
                    . 'central idea. Return only the statement, no preamble.'],
                ['type' => 'image_url', 'image_url' => ['url' => $dataUri]],
            ]],
        ], 200);
    }

    /**
     * Pull readable text out of a PDF so Alice can summarise the real content,
     * not just the metadata. Returns a trimmed excerpt (empty on failure).
     */
    public function extractPdfText(string $absolutePath, int $maxChars = 8000): string
    {
        if (! is_file($absolutePath)) {
            return '';
        }

        try {
            $pdf = (new \Smalot\PdfParser\Parser())->parseFile($absolutePath);
            $text = $pdf->getText();
        } catch (\Throwable $e) {
            report($e);

            return '';
        }

        $text = trim((string) preg_replace('/\s+/', ' ', $text));

        return mb_substr($text, 0, $maxChars);
    }

    /**
     * Alice AI: from whatever bibliographic metadata a member has entered for a
     * Knowledge Database entry, draft an abstract and suggest a topic, keywords
     * and categories. Returns a structured array; missing keys mean "no
     * suggestion". Never invents authors, figures or facts not implied by the
     * material.
     *
     * @param  array<string, mixed>  $meta
     * @return array{abstract?: string, topic?: string, keywords?: array<int, string>, categories?: array<int, string>}
     */
    public function describeLibraryEntry(array $meta): array
    {
        $lines = [];
        foreach ($meta as $label => $value) {
            $value = is_array($value) ? implode(', ', $value) : (string) $value;
            if (trim($value) !== '') {
                $lines[] = "{$label}: {$value}";
            }
        }
        $material = implode("\n", $lines) ?: '(no metadata provided)';

        $topics = Topic::query()->pluck('name')->implode(', ');

        $raw = $this->chat([
            ['role' => 'system', 'content' => self::SYSTEM],
            ['role' => 'user', 'content' =>
                "You are Alice, building a research knowledge database. From the bibliographic "
                . "material below, produce a JSON object with these keys:\n"
                . "- \"abstract\": a concise 80–150 word abstract/summary of what this work is about. "
                . "Base it ONLY on the material; do not invent findings, figures or claims. If there is "
                . "too little to summarise, return an empty string.\n"
                . "- \"topic\": a single short research topic label (prefer one of the existing topics if it fits: "
                . ($topics !== '' ? $topics : 'none yet') . ").\n"
                . "- \"keywords\": an array of 4–8 short keyword strings.\n"
                . "- \"categories\": an array of 1–3 short thematic category strings.\n"
                . "Return ONLY the JSON object, no prose, no code fences.\n\n"
                . "MATERIAL:\n{$material}",
            ],
        ], maxTokens: 600);

        // Strip any accidental code fences, then decode.
        $json = trim(preg_replace('/^```(?:json)?|```$/m', '', $raw) ?? $raw);
        $data = json_decode($json, true);

        if (! is_array($data)) {
            return [];
        }

        return array_filter([
            'abstract' => isset($data['abstract']) ? trim((string) $data['abstract']) : null,
            'topic' => isset($data['topic']) ? trim((string) $data['topic']) : null,
            'keywords' => isset($data['keywords']) && is_array($data['keywords'])
                ? array_values(array_filter(array_map('trim', $data['keywords'])))
                : null,
            'categories' => isset($data['categories']) && is_array($data['categories'])
                ? array_values(array_filter(array_map('trim', $data['categories'])))
                : null,
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    public function chat(array $messages, int $maxTokens = 700): string
    {
        $response = Http::baseUrl(rtrim((string) config('ai.base_url'), '/'))
            ->withToken((string) config('ai.key'))
            ->timeout((int) config('ai.timeout', 90))
            ->acceptJson()
            ->post('/chat/completions', [
                'model' => config('ai.model'),
                'messages' => $messages,
                'temperature' => 0.4,
                'max_tokens' => $maxTokens,
            ])
            ->throw();

        return trim((string) $response->json('choices.0.message.content'));
    }

    /**
     * Render an idea + its human discussion as plain text for the prompt.
     * AI-authored comments are excluded so the model never feeds on itself.
     */
    private function ideaContext(ResearchConcept $idea, bool $withDiscussion = true): string
    {
        $idea->loadMissing(['topic', 'comments.user']);

        $parts = ["Title: {$idea->title}"];

        if ($idea->topic) {
            $parts[] = "Topic: {$idea->topic->name}";
        }

        $parts[] = "Status: {$idea->status}";
        $parts[] = "\nIdea body:\n" . ($idea->body ?: '(no body written yet)');

        if ($withDiscussion) {
            $aiAuthorId = User::aiAuthor()->id;

            $discussion = $idea->comments
                ->where('user_id', '!=', $aiAuthorId)
                ->map(fn ($c): string => '- ' . ($c->user?->name ?? 'Someone') . ': ' . $c->body)
                ->implode("\n");

            $parts[] = "\nDiscussion so far:\n" . ($discussion !== '' ? $discussion : '(no comments yet)');
        }

        return implode("\n", $parts);
    }

    /**
     * Render a topic, its ideas and its own discussion as plain text.
     * AI-authored comments are excluded so the model never feeds on itself.
     */
    private function topicContext(Topic $topic): string
    {
        $topic->loadMissing([
            'researchConcepts' => fn ($q) => $q->latest()->limit(40),
            'comments.user',
        ]);

        $parts = ["Topic: {$topic->name}"];

        if ($topic->description) {
            $parts[] = "Description: {$topic->description}";
        }

        $ideas = $topic->researchConcepts
            ->map(fn (ResearchConcept $i): string => "- [{$i->status}] {$i->title}: " . (string) str($i->body)->limit(160))
            ->implode("\n");

        $parts[] = "\nIdeas under this topic:\n" . ($ideas !== '' ? $ideas : '(no ideas yet)');

        $aiAuthorId = User::aiAuthor()->id;

        $discussion = $topic->comments
            ->where('user_id', '!=', $aiAuthorId)
            ->map(fn ($c): string => '- ' . ($c->user?->name ?? 'Someone') . ': ' . $c->body)
            ->implode("\n");

        if ($discussion !== '') {
            $parts[] = "\nDiscussion on this topic:\n" . $discussion;
        }

        return implode("\n", $parts);
    }
}
