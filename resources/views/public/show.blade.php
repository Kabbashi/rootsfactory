@extends('public.layout')

@section('title', $idea->title)
@section('description', Str::limit(strip_tags(\Illuminate\Support\Str::markdown($idea->body ?? '')), 155))

@section('content')
    <article class="mx-auto max-w-3xl px-6 py-14">
        <a href="{{ route('publications.index') }}" class="text-sm font-medium text-root-600 hover:text-root-900">← All publications</a>

        <header class="mt-6 border-b border-root-100 pb-8">
            @if ($idea->topic)
                <a href="{{ route('publications.index', ['topic' => $idea->topic->slug]) }}"
                   class="text-xs font-semibold uppercase tracking-wide text-root-600 hover:text-root-900">
                    {{ $idea->topic->name }}
                </a>
            @endif
            <h1 class="mt-3 font-serif text-4xl font-bold leading-tight text-root-900">{{ $idea->title }}</h1>
            <p class="mt-4 text-sm text-root-600">
                By {{ $idea->user?->name ?? 'Roots Factory' }}
                @if ($idea->published_at)
                    · Published {{ $idea->published_at->format('j F Y') }}
                @endif
            </p>
        </header>

        <div class="prose mt-8 max-w-none">
            {!! \Illuminate\Support\Str::markdown($idea->body ?? '_No content._') !!}
        </div>

        <footer class="mt-12 rounded-2xl bg-root-100/50 px-6 py-6 text-sm text-root-700">
            <p class="font-serif text-base text-root-800">From the workshop to the world.</p>
            <p class="mt-1">
                This brief grew out of an internal discussion in the Roots Factory team workspace.
                <a href="{{ url('/workspace') }}" class="font-medium text-root-800 underline">Join the conversation →</a>
            </p>
        </footer>
    </article>

    @if ($related->isNotEmpty())
        <section class="border-t border-root-100">
            <div class="mx-auto max-w-3xl px-6 py-12">
                <h2 class="font-serif text-2xl font-semibold text-root-900">Related publications</h2>
                <ul class="mt-6 space-y-4">
                    @foreach ($related as $other)
                        <li>
                            <a href="{{ route('publications.show', $other) }}" class="group flex items-baseline justify-between gap-4">
                                <span class="font-serif text-lg text-root-800 group-hover:underline">{{ $other->title }}</span>
                                @if ($other->published_at)
                                    <span class="shrink-0 text-xs text-root-600">{{ $other->published_at->format('j M Y') }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endsection
