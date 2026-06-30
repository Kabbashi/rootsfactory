@extends('public.layout')

@section('title', 'Publications')
@section('description', 'Working papers, research papers, policy briefs and essays from the Roots Factory research network — open and source-backed.')

@section('content')
    <section class="border-b border-root-100 bg-root-100/40">
        <div class="mx-auto max-w-5xl px-6 py-16">
            <p class="font-sans text-sm font-semibold uppercase tracking-wide text-root-600">Roots Factory</p>
            <h1 class="mt-3 max-w-2xl font-serif text-4xl font-bold leading-tight text-root-900 sm:text-5xl">
                Collaborative research, published openly.
            </h1>
            <p class="mt-4 max-w-2xl text-lg text-root-700">
                An independent international research network for qualitative evidence and local
                perspectives. What the network develops together, we publish here — papers, briefs and essays.
            </p>
        </div>
    </section>

    <div class="mx-auto max-w-5xl px-6 py-12">
        {{-- Featured lead --}}
        @if ($featured)
            <a href="{{ route('publications.show', $featured) }}"
               class="group mb-12 block rounded-3xl border border-root-100 bg-white p-8 transition hover:border-root-600/40 hover:shadow-md sm:p-10">
                <span class="rounded-full bg-root-800 px-3 py-1 text-xs font-semibold text-root-50">{{ $featured->typeLabel() }}</span>
                <h2 class="mt-4 font-serif text-3xl font-bold leading-tight text-root-900 group-hover:underline sm:text-4xl">
                    {{ $featured->title }}
                </h2>
                <p class="mt-4 max-w-3xl text-root-700">
                    {{ Str::limit(strip_tags(\Illuminate\Support\Str::markdown($featured->abstract ?? $featured->body ?? '')), 240) }}
                </p>
                <p class="mt-6 text-sm text-root-600">
                    @php($shownAuthors = $featured->authors->where('profile_public', true))
                    {{ $shownAuthors->isNotEmpty() ? $shownAuthors->pluck('name')->join(', ') : 'Roots Factory' }}
                    @if ($featured->published_at) · {{ $featured->published_at->format('j M Y') }} @endif
                </p>
            </a>
        @endif

        {{-- Type filter --}}
        <div class="mb-10 flex flex-wrap gap-2 border-b border-root-100 pb-6 text-sm">
            <a href="{{ route('publications.index') }}"
               class="rounded-full px-4 py-1.5 font-medium {{ $isFiltered ? 'bg-root-100 text-root-700 hover:bg-root-200' : 'bg-root-800 text-root-50' }}">All</a>
            @foreach ($types as $value => $label)
                <a href="{{ route('publications.index', ['type' => $value]) }}"
                   class="rounded-full px-4 py-1.5 font-medium {{ $filters['type'] === $value ? 'bg-root-800 text-root-50' : 'bg-root-100 text-root-700 hover:bg-root-200' }}">{{ $label }}</a>
            @endforeach
        </div>

        {{-- Grid --}}
        @if ($publications->isNotEmpty())
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($publications as $publication)
                    @include('public.partials.card', ['publication' => $publication])
                @endforeach
            </div>
        @elseif (! $featured)
            <div class="rounded-2xl border border-dashed border-root-100 bg-white px-6 py-16 text-center">
                <p class="font-serif text-xl text-root-800">{{ $isFiltered ? 'Nothing matches that type.' : 'Nothing published yet.' }}</p>
                <p class="mt-2 text-root-700">
                    {{ $isFiltered ? 'Try a different type.' : 'The network is still at work. Check back soon.' }}
                </p>
            </div>
        @endif

        @if ($publications->hasPages())
            <div class="mt-12">{{ $publications->links() }}</div>
        @endif
    </div>
@endsection
