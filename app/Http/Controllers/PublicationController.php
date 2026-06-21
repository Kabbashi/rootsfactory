<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\Region;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The public face of the think-tank: ideas the team has matured and published,
 * plus the people behind them. Everything here is read-only and needs no
 * authentication — the "from an internal thought → to public evidence" layer.
 */
class PublicationController extends Controller
{
    /** A curated feed: a featured lead, then a filterable grid of publications. */
    public function index(Request $request): View
    {
        $filters = [
            'topic' => $request->string('topic')->value() ?: null,
            'region' => $request->string('region')->value() ?: null,
            'type' => $request->string('type')->value() ?: null,
        ];
        $isFiltered = (bool) array_filter($filters);

        $query = fn () => Idea::published()
            ->with(['user', 'topic', 'region'])
            ->when($filters['topic'], fn ($q, $s) => $q->whereRelation('topic', 'slug', $s))
            ->when($filters['region'], fn ($q, $s) => $q->whereRelation('region', 'slug', $s))
            ->when($filters['type'], fn ($q, $s) => $q->where('type', $s));

        // The lead is the newest pinned publication, else the newest one —
        // but only on the unfiltered front page, so filtering stays a clean list.
        $featured = $isFiltered
            ? null
            : (clone $query())->orderByDesc('pinned')->latest('published_at')->first();

        $publications = $query()
            ->when($featured, fn ($q) => $q->whereKeyNot($featured->getKey()))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('public.index', [
            'featured' => $featured,
            'publications' => $publications,
            'topics' => Topic::whereHas('ideas', fn ($q) => $q->published())->orderBy('name')->get(),
            'regions' => Region::whereHas('ideas', fn ($q) => $q->published())->orderBy('name')->get(),
            'types' => Idea::TYPES,
            'filters' => $filters,
            'isFiltered' => $isFiltered,
        ]);
    }

    /** A single publication, rendered as an article. */
    public function show(Idea $idea): View
    {
        abort_unless($idea->status === 'published', 404);

        $idea->load(['user', 'topic', 'region']);

        $related = Idea::published()
            ->where('id', '!=', $idea->id)
            ->when($idea->topic_id, fn ($q) => $q->where('topic_id', $idea->topic_id))
            ->with(['topic'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('public.show', [
            'idea' => $idea,
            'related' => $related,
        ]);
    }

    /** A public author profile: bio plus their published work. */
    public function person(User $user): View
    {
        abort_unless($user->isPublicAuthor(), 404);

        return view('public.person', [
            'person' => $user,
            'publications' => $user->publishedIdeas()->with(['topic', 'region'])->paginate(9),
        ]);
    }
}
