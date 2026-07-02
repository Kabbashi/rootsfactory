<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Document the "Ask AI" assistant and its four agents on a Research Concept as
 * a FAQ entry, so members know what each one does and that a human always
 * reviews before anything is kept or published.
 */
return new class extends Migration
{
    private string $question = 'What does “Ask AI” do on a Research Concept, and what are the four agents?';

    public function up(): void
    {
        if (DB::table('faqs')->where('question', $this->question)->exists()) {
            return;
        }

        $answer = <<<'MD'
        On a **Research Concept’s edit page** you’ll find an **“Ask AI”** button. It offers four assistants (agents) that help you think — they never make editorial decisions, and **nothing is published automatically**. A person always reviews and saves.

        **1. Summarize** — Writes a concise brief of the concept and the human discussion so far (core proposition, key points, open questions). The summary is **posted into the discussion thread** below; reload to see it.

        **2. Red-team** — Acts as a constructive critic: it names the 3–5 strongest challenges, risks or blind spots, and ends with the single most important question to resolve next. The response is **posted into the discussion**.

        **3. Find related ideas** — Scans the workspace and lists the concepts/ideas most genuinely related to this one, with a line on how each connects. If nothing is clearly related, it says so. Posted into the **discussion**.

        **4. Expand into a brief** — Rewrites the concept’s body into a fuller, structured brief (problem, proposal, how it works, who’s involved, open questions). The draft is **loaded into the editor but not saved** — check it, edit anything, then **Save** yourself to keep it.

        Notes:
        - Agents 1–3 add their reply to the discussion; agent 4 drafts directly in the editor.
        - The AI is source-faithful: it won’t invent statistics, figures, dates, organisations or citations.
        - When a concept is **Final (locked)**, the editing agents are hidden.
        MD;

        DB::table('faqs')->insert([
            'question' => $this->question,
            'answer' => $answer,
            'category' => 'AI',
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
