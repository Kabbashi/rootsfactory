{{-- A publication card. Expects $idea (with topic, region, user loaded). --}}
<article class="flex flex-col rounded-2xl border border-root-100 bg-white p-6 transition hover:border-root-600/40 hover:shadow-sm">
    <div class="flex flex-wrap items-center gap-2">
        <span class="rounded-full bg-root-800 px-2.5 py-0.5 text-xs font-semibold text-root-50">{{ $idea->typeLabel() }}</span>
        @if ($idea->topic)
            <a href="{{ route('publications.index', ['topic' => $idea->topic->slug]) }}"
               class="text-xs font-semibold uppercase tracking-wide text-root-600 hover:text-root-900">{{ $idea->topic->name }}</a>
        @endif
        @if ($idea->region)
            <span class="text-xs text-root-600">· {{ $idea->region->name }}</span>
        @endif
    </div>

    <h2 class="mt-3 font-serif text-xl font-semibold leading-snug text-root-900">
        <a href="{{ route('publications.show', $idea) }}" class="hover:underline">{{ $idea->title }}</a>
    </h2>

    <p class="mt-3 flex-1 text-sm leading-relaxed text-root-700">
        {{ Str::limit(strip_tags(\Illuminate\Support\Str::markdown($idea->body ?? '')), 150) }}
    </p>

    <p class="mt-5 text-xs text-root-600">
        @if ($idea->user)
            <a href="{{ route('people.show', $idea->user) }}" class="font-medium text-root-700 hover:text-root-900 hover:underline">{{ $idea->user->name }}</a>
        @else
            Roots Factory
        @endif
        @if ($idea->published_at) · {{ $idea->published_at->format('j M Y') }} @endif
    </p>
</article>
