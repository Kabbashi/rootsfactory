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
        {{-- Featured lead --}}
        @if ($featured)
            <a href="{{ route('publications.show', $featured) }}"
               class="group mb-12 block rounded-3xl border border-root-100 bg-white p-8 transition hover:border-root-600/40 hover:shadow-md sm:p-10">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-root-800 px-3 py-1 text-xs font-semibold text-root-50">{{ $featured->typeLabel() }}</span>
                    @if ($featured->topic)
                        <span class="text-xs font-semibold uppercase tracking-wide text-root-600">{{ $featured->topic->name }}</span>
                    @endif
                    @if ($featured->region)
                        <span class="text-xs text-root-600">· {{ $featured->region->name }}</span>
                    @endif
                </div>
                <h2 class="mt-4 font-serif text-3xl font-bold leading-tight text-root-900 group-hover:underline sm:text-4xl">
                    {{ $featured->title }}
                </h2>
                <p class="mt-4 max-w-3xl text-root-700">
                    {{ Str::limit(strip_tags(\Illuminate\Support\Str::markdown($featured->body ?? '')), 240) }}
                </p>
                <p class="mt-6 text-sm text-root-600">
                    {{ $featured->user?->name ?? 'Roots Factory' }}
                    @if ($featured->published_at) · {{ $featured->published_at->format('j M Y') }} @endif
                </p>
            </a>
        @endif
        {{-- Note: the featured lead is one big clickable link, so the author name
             stays plain text here; author links live on the cards and article. --}}

        {{-- Filter bar --}}
        @if ($topics->isNotEmpty() || $regions->isNotEmpty())
            <div class="mb-10 flex flex-col gap-4 border-b border-root-100 pb-6">
                @if ($topics->isNotEmpty())
                    <nav class="flex flex-wrap gap-2 text-sm">
                        <a href="{{ route('publications.index') }}"
                           class="rounded-full px-4 py-1.5 font-medium {{ $isFiltered ? 'bg-root-100 text-root-700 hover:bg-root-200' : 'bg-root-800 text-root-50' }}">All</a>
                        @foreach ($topics as $topic)
                            <a href="{{ route('publications.index', array_filter(['topic' => $topic->slug, 'region' => $filters['region'], 'type' => $filters['type']])) }}"
                               class="rounded-full px-4 py-1.5 font-medium {{ $filters['topic'] === $topic->slug ? 'bg-root-800 text-root-50' : 'bg-root-100 text-root-700 hover:bg-root-200' }}">{{ $topic->name }}</a>
                        @endforeach
                    </nav>
                @endif

                @if ($regions->isNotEmpty() || count($types))
                    <form method="GET" action="{{ route('publications.index') }}" class="flex flex-wrap items-center gap-3 text-sm">
                        @if ($filters['topic']) <input type="hidden" name="topic" value="{{ $filters['topic'] }}"> @endif

                        @if ($regions->isNotEmpty())
                            <label class="flex items-center gap-2 text-root-600">Region
                                <select name="region" onchange="this.form.submit()" class="rounded-lg border border-root-100 bg-white px-3 py-1.5 text-root-800">
                                    <option value="">All</option>
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->slug }}" @selected($filters['region'] === $region->slug)>{{ $region->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endif

                        <label class="flex items-center gap-2 text-root-600">Type
                            <select name="type" onchange="this.form.submit()" class="rounded-lg border border-root-100 bg-white px-3 py-1.5 text-root-800">
                                <option value="">All</option>
                                @foreach ($types as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <noscript><button class="rounded-lg bg-root-800 px-3 py-1.5 text-root-50">Apply</button></noscript>
                    </form>
                @endif
            </div>
        @endif

        {{-- Grid --}}
        @if ($publications->isNotEmpty())
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($publications as $idea)
                    @include('public.partials.card', ['idea' => $idea])
                @endforeach
            </div>
        @elseif (! $featured)
            <div class="rounded-2xl border border-dashed border-root-100 bg-white px-6 py-16 text-center">
                <p class="font-serif text-xl text-root-800">{{ $isFiltered ? 'Nothing matches that filter.' : 'Nothing published yet.' }}</p>
                <p class="mt-2 text-root-700">
                    {{ $isFiltered ? 'Try a different topic, region or type.' : 'The team is still thinking out loud in the workspace. Check back soon.' }}
                </p>
            </div>
        @endif

        @if ($publications->hasPages())
            <div class="mt-12">{{ $publications->links() }}</div>
        @endif
    </div>
@endsection
