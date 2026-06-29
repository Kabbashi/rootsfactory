@extends('public.layout')

@section('title', 'Community')
@section('description', 'The Roots Factory community — a scholarly network of researchers, consultants and authors.')

@section('content')
    <section class="border-b border-root-100 bg-root-100/40">
        <div class="mx-auto max-w-5xl px-6 py-14">
            <p class="font-sans text-sm font-semibold uppercase tracking-wide text-root-600">Community</p>
            <h1 class="mt-3 font-serif text-4xl font-bold leading-tight text-root-900">A network of researchers.</h1>
            <p class="mt-4 max-w-2xl text-root-700">
                Researchers, consultants and authors across Africa, Asia and beyond — working together
                on qualitative evidence and local knowledge.
            </p>
        </div>
    </section>

    <div class="mx-auto max-w-5xl px-6 py-12">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($members as $member)
                <article class="flex items-start gap-4 rounded-2xl border border-root-100 bg-white p-5">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-root-800 font-serif text-lg font-bold text-root-50">
                        {{ Str::upper(Str::substr($member->name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="font-serif text-lg font-semibold text-root-900">
                            @if ($member->isPublicAuthor())
                                <a href="{{ route('people.show', $member) }}" class="hover:underline">{{ $member->name }}</a>
                            @else
                                {{ $member->name }}
                            @endif
                        </h2>
                        @if ($member->title)
                            <p class="text-sm text-root-600">{{ $member->title }}</p>
                        @endif
                        @if (! empty($member->expertise))
                            <p class="mt-2 text-xs text-root-600">{{ collect($member->expertise)->take(3)->join(', ') }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        @if ($members->hasPages())
            <div class="mt-12">{{ $members->links() }}</div>
        @endif
    </div>
@endsection
