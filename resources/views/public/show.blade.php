@extends('public.layout')

@section('title', $publication->title)
@section('description', Str::limit(strip_tags(\Illuminate\Support\Str::markdown($publication->abstract ?? $publication->body ?? '')), 155))

@section('content')
    <article class="mx-auto max-w-3xl px-6 py-14">
        <a href="{{ route('publications.index') }}" class="text-sm font-medium text-root-600 hover:text-root-900">← All publications</a>

        <header class="mt-6 border-b border-root-100 pb-8">
            <span class="rounded-full bg-root-800 px-3 py-1 text-xs font-semibold text-root-50">{{ $publication->typeLabel() }}</span>
            <h1 class="mt-3 font-serif text-4xl font-bold leading-tight text-root-900">{{ $publication->title }}</h1>
            <p class="mt-4 text-sm text-root-600">
                By
                @if ($publication->authors->isNotEmpty())
                    @foreach ($publication->authors as $author)<a href="{{ route('people.show', $author) }}" class="font-medium text-root-700 hover:text-root-900 hover:underline">{{ $author->name }}</a>@if (! $loop->last), @endif @endforeach
                @else
                    Roots Factory
                @endif
                @if ($publication->published_at) · Published {{ $publication->published_at->format('j F Y') }} @endif
            </p>
            @if ($publication->project)
                <p class="mt-2 text-sm text-root-600">
                    From the project <a href="{{ route('research.show', $publication->project) }}" class="text-root-700 hover:underline">{{ $publication->project->title }}</a>
                </p>
            @endif
        </header>

        @if ($publication->abstract)
            <div class="mt-8 rounded-2xl bg-root-100/50 px-6 py-5">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-root-600">Abstract</h2>
                <p class="mt-2 text-root-800">{{ $publication->abstract }}</p>
            </div>
        @endif

        <div class="prose mt-8 max-w-none">
            {!! \Illuminate\Support\Str::markdown($publication->body ?? '_No content yet._') !!}
        </div>

        @if ($publication->citation || $publication->doi)
            <footer class="mt-12 rounded-2xl border border-root-100 px-6 py-6 text-sm text-root-700">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-root-600">How to cite</h2>
                @if ($publication->citation)
                    <p class="mt-2">{{ $publication->citation }}</p>
                @endif
                @if ($publication->doi)
                    <p class="mt-1 font-mono text-xs">{{ $publication->doi }}</p>
                @endif
            </footer>
        @endif
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
