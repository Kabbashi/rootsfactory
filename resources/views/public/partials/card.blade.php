{{-- A publication card. Expects $publication (with authors loaded). --}}
<article class="flex flex-col rounded-2xl border border-root-100 bg-white p-6 transition hover:border-root-600/40 hover:shadow-sm">
    <div class="flex flex-wrap items-center gap-2">
        <span class="rounded-full bg-root-800 px-2.5 py-0.5 text-xs font-semibold text-root-50">{{ $publication->typeLabel() }}</span>
    </div>

    <h2 class="mt-3 font-serif text-xl font-semibold leading-snug text-root-900">
        <a href="{{ route('publications.show', $publication) }}" class="hover:underline">{{ $publication->title }}</a>
    </h2>

    <p class="mt-3 flex-1 text-sm leading-relaxed text-root-700">
        {{ Str::limit(strip_tags(\Illuminate\Support\Str::markdown($publication->abstract ?? $publication->body ?? '')), 150) }}
    </p>

    <p class="mt-5 text-xs text-root-600">
        @if ($publication->authors->isNotEmpty())
            @foreach ($publication->authors as $author)<a href="{{ route('people.show', $author) }}" class="font-medium text-root-700 hover:text-root-900 hover:underline">{{ $author->name }}</a>@if (! $loop->last), @endif @endforeach
        @else
            Roots Factory
        @endif
        @if ($publication->published_at) · {{ $publication->published_at->format('j M Y') }} @endif
    </p>
</article>
