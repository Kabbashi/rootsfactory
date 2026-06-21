<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The public face of the think-tank: ideas the team has matured and published.
 * Everything here is read-only and needs no authentication — this is the
 * "from an internal thought → to public evidence" layer of the Concept Note.
 */
class PublicationController extends Controller
{
    /** A list of published ideas, newest first, optionally filtered by topic. */
    public function index(Request $request): View
    {
        $topics = Topic::whereHas('ideas', fn ($q) => $q->published())
            ->orderBy('name')
            ->get();

        $publications = Idea::published()
            ->with(['user', 'topic'])
            ->when(
                $request->filled('topic'),
                fn ($q) => $q->whereRelation('topic', 'slug', $request->string('topic')),
            )
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('public.index', [
            'publications' => $publications,
            'topics' => $topics,
            'activeTopic' => $request->string('topic')->value() ?: null,
        ]);
    }

    /** A single publication, rendered as an article. */
    public function show(Idea $idea): View
    {
        abort_unless($idea->status === 'published', 404);

        $idea->load(['user', 'topic']);

        $related = Idea::published()
            ->where('id', '!=', $idea->id)
            ->when($idea->topic_id, fn ($q) => $q->where('topic_id', $idea->topic_id))
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('public.show', [
            'idea' => $idea,
            'related' => $related,
        ]);
    }
}
