<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FAQ: the difference between a Research Concept and a Research Project, and
 * how work flows from one to the other.
 */
return new class extends Migration
{
    private string $question = 'What is the difference between a Research Concept and a Research Project?';

    public function up(): void
    {
        if (DB::table('faqs')->where('question', $this->question)->exists()) {
            return;
        }

        $answer = <<<'MD'
        They are two stages of the same journey: **a Research Concept is the thinking; a Research Project is the doing.**

        **Research Concept — the idea, worked out on paper.**
        A lightweight brief that answers *what* and *why*. It usually grows from an item in the Idea Pool, is discussed by the team, and can be sharpened with the “Ask AI” agents. It moves **draft → in discussion → final**; once **Final** it is locked, and only the person who brought it in can change it. A concept holds text, topic, categories and keywords — not data or a work plan. When a concept is solid, it is handed off to become a project.

        **Research Project — the actual research work.**
        The unit where the research happens. It answers *how*: a team with roles, objectives, research questions, methodology, a data-collection plan, a timeline (start/end), and — in the Data Hub — real evidence (transcripts, field notes, coded data). It moves **planned → active → completed** (and can be archived). A project is a collaborative workspace with documents, tasks and references, and it **produces one or more Publications**.

        **The flow**

        ```
        Idea  →  Research Concept  →  Research Project  →  Publication
        (spark)   (the proposal)      (the work + data)     (the output)
        ```

        **In short**

        | | Research Concept | Research Project |
        |---|---|---|
        | Purpose | Frame the idea (what/why) | Carry out the research (how) |
        | Contains | Text, topic, categories, keywords | Team, methodology, data, tasks, timeline |
        | Status | draft → in discussion → final | planned → active → completed |
        | Produces | A ready proposal → a project | Findings → Publications |

        You don’t create a project from scratch as a rule — you grow it from a concept once the concept is mature.
        MD;

        DB::table('faqs')->insert([
            'question' => $this->question,
            'answer' => $answer,
            'category' => 'Research',
            'sort' => (int) DB::table('faqs')->max('sort') + 1,
            'published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('faqs')->where('question', $this->question)->delete();
    }
};
