<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The public face of the think-tank: the scholarship the editorial office has
 * published, and the people behind it. Everything here is read-only and needs
 * no authentication.
 */
class PublicationController extends Controller
{
    /** The home page: a featured lead, then a filterable grid of publications. */
    public function index(Request $request): View
    {
        $type = $request->string('type')->value() ?: null;
        $isFiltered = (bool) $type;

        $query = fn () => Publication::published()
            ->with('authors')
            ->when($type, fn ($q, $t) => $q->where('type', $t));

        $featured = $isFiltered ? null : (clone $query())->latest('published_at')->first();

        $publications = $query()
            ->when($featured, fn ($q) => $q->whereKeyNot($featured->getKey()))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('public.index', [
            'featured' => $featured,
            'publications' => $publications,
            'types' => Publication::TYPES,
            'filters' => ['type' => $type],
            'isFiltered' => $isFiltered,
        ]);
    }

    /** A single publication, rendered as an article. */
    public function show(Publication $publication): View
    {
        abort_unless($publication->status === 'published', 404);

        $publication->load(['authors', 'project']);

        $related = Publication::published()
            ->whereKeyNot($publication->getKey())
            ->when($publication->type, fn ($q) => $q->where('type', $publication->type))
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('public.show', [
            'publication' => $publication,
            'related' => $related,
        ]);
    }

    /** A public member profile: bio, scholarly details and published work. */
    public function person(User $user): View
    {
        abort_unless($user->isPublicAuthor(), 404);

        return view('public.person', [
            'person' => $user,
            'publications' => $user->publishedPublications()->paginate(9),
        ]);
    }
}
