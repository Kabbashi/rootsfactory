<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FAQ: how work moves Idea -> Concept -> Project, and which button does what.
 */
return new class extends Migration
{
    private string $question = 'How do I move work from an Idea to a Concept to a Project?';

    public function up(): void
    {
        if (DB::table('faqs')->where('question', $this->question)->exists()) {
            return;
        }

        $answer = <<<'MD'
        Work grows in three promotions. Each one **carries the important content forward and leaves the source in place** — nothing is deleted, and the link back is remembered.

        **Idea → Research Concept**
        Open an idea in the **Idea Pool** and use **“Move to Research Concept”**. This creates a **draft concept** that carries the idea’s text, categories and keywords, and records the idea as its origin. The idea stays in the pool.
        *Alternatively:* on the **Alice AI** page choose “New research concept” and, optionally, base it on an existing idea — Alice drafts the concept from it.

        **Research Concept → Research Project**
        Open a concept and use **“Grow into a Research Project”**. This creates a **planned project** that carries the concept’s title, its text (as the project summary) and its categories, and records the concept as its origin. The concept stays unchanged. There is **one project per concept** — if you click it again, you’re taken to the project that already exists.

        **Research Project → Publication**
        A project produces one or more **Publications** through the **Editorial Office** workflow (draft → review → published).

        **The whole path**

        ```
        Idea  →  Research Concept  →  Research Project  →  Publication
        Move to Concept   Grow into a Project    Editorial Office
        ```

        Tip: you normally promote a concept once it is mature (often when it is marked **Final**), but the button is available whenever you’re ready.
        MD;

        DB::table('faqs')->insert([
            'question' => $this->question,
            'answer' => $answer,
            'category' => 'Getting started',
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
