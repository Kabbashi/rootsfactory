<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Services\CoThinker;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Public Q&A (Phase 3): visitors ask a question and get an answer grounded in
 * Roots Factory's own published briefs, with cited sources. No login required.
 *
 * Retrieval is Postgres full-text search over published ideas — no vector DB,
 * no infra change. The LLM is only called when the corpus actually matches the
 * question, so off-topic queries cost nothing and can never be answered from
 * thin air.
 */
class AskController extends Controller
{
    public function ask(Request $request): View
    {
        $question = Str::limit(trim((string) $request->query('q', '')), 300, '');
        $answer = null;
        $error = false;
        $sources = collect();

        if ($question !== '') {
            $sources = $this->search($question);

            if ($sources->isNotEmpty()) {
                try {
                    $answer = app(CoThinker::class)->answerQuestion($question, $sources);
                } catch (\Throwable $e) {
                    report($e);
                    $error = true;
                }
            }
        }

        return view('public.ask', compact('question', 'answer', 'sources', 'error'));
    }

    /**
     * Top published publications matching the question, ranked by full-text relevance.
     *
     * @return \Illuminate\Support\Collection<int, Publication>
     */
    private function search(string $question): \Illuminate\Support\Collection
    {
        $tsv = "to_tsvector('english', coalesce(title, '') || ' ' || coalesce(abstract, '') || ' ' || coalesce(body, ''))";

        return Publication::published()
            ->with('authors')
            ->selectRaw("publications.*, ts_rank({$tsv}, plainto_tsquery('english', ?)) as rank", [$question])
            ->whereRaw("{$tsv} @@ plainto_tsquery('english', ?)", [$question])
            ->orderByDesc('rank')
            ->limit(5)
            ->get();
    }
}
