@extends('public.layout')

@section('title', 'Publications')
@section('description', 'Published briefs, studies and analysis from the Roots Factory team — from internal thinking to public evidence.')

@section('content')
    <section class="border-b border-root-100 bg-root-100/40">
        <div class="mx-auto max-w-5xl px-6 py-16">
            <p class="font-sans text-sm font-semibold uppercase tracking-wide text-root-600">Roots Factory</p>
            <h1 class="mt-3 max-w-2xl font-serif text-4xl font-bold leading-tight text-root-900 sm:text-5xl">
                From an internal thought to public evidence.
            </h1>
            <p class="mt-4 max-w-2xl text-lg text-root-700">
                A grassroots think-tank for development cooperation. What the team matures in the
                workspace, we publish here openly — briefs, studies and analysis.
            </p>
        </div>
    </section>

    <div class="mx-auto max-w-5xl px-6 py-12">
        @if ($topics->isNotEmpty())
            <nav class="mb-10 flex flex-wrap gap-2 text-sm">
                <a href="{{ route('publications.index') }}"
                   class="rounded-full px-4 py-1.5 font-medium {{ $activeTopic ? 'bg-root-100 text-root-700 hover:bg-root-200' : 'bg-root-800 text-root-50' }}">
                    All
                </a>
                @foreach ($topics as $topic)
                    <a href="{{ route('publications.index', ['topic' => $topic->slug]) }}"
                       class="rounded-full px-4 py-1.5 font-medium {{ $activeTopic === $topic->slug ? 'bg-root-800 text-root-50' : 'bg-root-100 text-root-700 hover:bg-root-200' }}">
                        {{ $topic->name }}
                    </a>
                @endforeach
            </nav>
        @endif

        @forelse ($publications as $idea)
            @if ($loop->first)
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @endif
                <article class="flex flex-col rounded-2xl border border-root-100 bg-white p-6 transition hover:border-root-600/40 hover:shadow-sm">
                    @if ($idea->topic)
                        <p class="text-xs font-semibold uppercase tracking-wide text-root-600">{{ $idea->topic->name }}</p>
                    @endif
                    <h2 class="mt-2 font-serif text-xl font-semibold leading-snug text-root-900">
                        <a href="{{ route('publications.show', $idea) }}" class="hover:underline">{{ $idea->title }}</a>
                    </h2>
                    <p class="mt-3 flex-1 text-sm leading-relaxed text-root-700">
                        {{ Str::limit(strip_tags(\Illuminate\Support\Str::markdown($idea->body ?? '')), 160) }}
                    </p>
                    <p class="mt-5 text-xs text-root-600">
                        {{ $idea->user?->name ?? 'Roots Factory' }}
                        @if ($idea->published_at) · {{ $idea->published_at->format('j M Y') }} @endif
                    </p>
                </article>
            @if ($loop->last)
                </div>
            @endif
        @empty
            <div class="rounded-2xl border border-dashed border-root-100 bg-white px-6 py-16 text-center">
                <p class="font-serif text-xl text-root-800">Nothing published yet.</p>
                <p class="mt-2 text-root-700">The team is still thinking out loud in the workspace. Check back soon.</p>
            </div>
        @endforelse

        @if ($publications->hasPages())
            <div class="mt-12">{{ $publications->links() }}</div>
        @endif
    </div>
@endsection
