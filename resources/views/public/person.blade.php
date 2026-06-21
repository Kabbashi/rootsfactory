@extends('public.layout')

@section('title', $person->name)
@section('description', $person->bio ? Str::limit(strip_tags($person->bio), 155) : $person->name . ' — author at Roots Factory.')

@section('content')
    <section class="border-b border-root-100 bg-root-100/40">
        <div class="mx-auto max-w-3xl px-6 py-14">
            <a href="{{ route('publications.index') }}" class="text-sm font-medium text-root-600 hover:text-root-900">← All publications</a>

            <div class="mt-6 flex items-start gap-5">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-root-800 font-serif text-2xl font-bold text-root-50">
                    {{ Str::upper(Str::substr($person->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="font-serif text-3xl font-bold text-root-900">{{ $person->name }}</h1>
                    @if ($person->title)
                        <p class="mt-1 text-root-700">{{ $person->title }}</p>
                    @endif
                </div>
            </div>

            @if ($person->bio)
                <p class="mt-6 max-w-2xl leading-relaxed text-root-700">{{ $person->bio }}</p>
            @endif
        </div>
    </section>

    <div class="mx-auto max-w-5xl px-6 py-12">
        <h2 class="mb-8 font-serif text-2xl font-semibold text-root-900">
            Publications <span class="text-root-600">({{ $publications->total() }})</span>
        </h2>

        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($publications as $idea)
                @include('public.partials.card', ['idea' => $idea->setRelation('user', $person)])
            @endforeach
        </div>

        @if ($publications->hasPages())
            <div class="mt-12">{{ $publications->links() }}</div>
        @endif
    </div>
@endsection
