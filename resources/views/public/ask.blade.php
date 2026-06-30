@extends('public.layout')

@section('title', 'Ask')
@section('description', 'Ask a question and get an answer grounded in Roots Factory’s published briefs, with cited sources.')

@section('content')
    <section class="border-b border-root-100 bg-root-100/40">
        <div class="mx-auto max-w-3xl px-6 py-14">
            <p class="font-sans text-sm font-semibold uppercase tracking-wide text-root-600">Ask Roots Factory</p>
            <h1 class="mt-3 font-serif text-4xl font-bold leading-tight text-root-900">
                Ask a question, get a cited answer.
            </h1>
            <p class="mt-4 max-w-2xl text-root-700">
                Answers are drawn only from our published briefs and cite their sources. If we haven’t
                published on something yet, we’ll say so rather than guess.
            </p>

            <form method="GET" action="{{ route('ask') }}" class="mt-8">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input type="text" name="q" value="{{ $question }}" maxlength="300" autofocus
                           placeholder="e.g. How can smallholder farmers be reached with climate finance?"
                           class="flex-1 rounded-xl border border-root-100 bg-white px-4 py-3 text-root-900 shadow-sm focus:border-root-600 focus:outline-none focus:ring-1 focus:ring-root-600">
                    <button type="submit" class="rounded-xl bg-root-800 px-6 py-3 font-medium text-root-50 hover:bg-root-900">Ask</button>
                </div>
            </form>
        </div>
    </section>

    <div class="mx-auto max-w-3xl px-6 py-12">
        @if ($question === '')
            <p class="text-root-600">Type a question above to get started.</p>
        @elseif ($error)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-6 py-8">
                <p class="font-serif text-lg text-root-900">The co-thinker is unavailable right now.</p>
                <p class="mt-2 text-root-700">Please try again in a moment.</p>
            </div>
        @elseif ($sources->isEmpty())
            <div class="rounded-2xl border border-dashed border-root-100 bg-white px-6 py-12 text-center">
                <p class="font-serif text-xl text-root-800">We haven’t published anything on that yet.</p>
                <p class="mt-2 text-root-700">
                    Try different wording, or <a href="{{ route('publications.index') }}" class="font-medium text-root-800 underline">browse the publications</a>.
                </p>
            </div>
        @else
            <article>
                <p class="text-sm font-semibold uppercase tracking-wide text-root-600">Question</p>
                <p class="mt-1 font-serif text-2xl font-semibold text-root-900">{{ $question }}</p>

                <div class="prose mt-6 max-w-none">
                    {!! \Illuminate\Support\Str::markdown($answer) !!}
                </div>

                <div class="mt-10 border-t border-root-100 pt-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-root-600">Sources</h2>
                    <ol class="mt-4 space-y-3">
                        @foreach ($sources as $i => $publication)
                            <li class="flex gap-3">
                                <span class="font-mono text-sm text-root-600">[{{ $i + 1 }}]</span>
                                <span>
                                    <a href="{{ route('publications.show', $publication) }}" class="font-medium text-root-800 hover:underline">{{ $publication->title }}</a>
                                    @php($shownAuthors = $publication->authors->where('profile_public', true))
                                    <span class="text-sm text-root-600">
                                        — {{ $publication->typeLabel() }}@if ($shownAuthors->isNotEmpty()), {{ $shownAuthors->pluck('name')->join(', ') }}@endif
                                    </span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </div>

                <p class="mt-8 text-xs text-root-600">
                    Generated from our published briefs by the Roots Factory co-thinker. It can make mistakes —
                    follow the sources for the full picture.
                </p>
            </article>
        @endif
    </div>
@endsection
