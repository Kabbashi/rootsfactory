@extends('public.layout')

@section('title', 'Research')
@section('description', 'Research projects, field studies, baseline studies and evaluations from the Roots Factory network.')

@section('content')
    <section class="border-b border-root-100 bg-root-100/40">
        <div class="mx-auto max-w-5xl px-6 py-14">
            <p class="font-sans text-sm font-semibold uppercase tracking-wide text-root-600">Research</p>
            <h1 class="mt-3 font-serif text-4xl font-bold leading-tight text-root-900">Projects across the network.</h1>
            <p class="mt-4 max-w-2xl text-root-700">
                Qualitative research, field studies, baselines and evaluations — grounded in local
                perspectives and shared across countries.
            </p>
        </div>
    </section>

    <div class="mx-auto max-w-5xl px-6 py-12">
        @if ($projects->isNotEmpty())
            <div class="grid gap-8 sm:grid-cols-2">
                @foreach ($projects as $project)
                    <article class="flex flex-col rounded-2xl border border-root-100 bg-white p-6 transition hover:border-root-600/40 hover:shadow-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-root-800 px-2.5 py-0.5 text-xs font-semibold text-root-50">{{ $project->kindLabel() }}</span>
                            @foreach ($project->regions as $region)
                                <span class="text-xs text-root-600">{{ $region->name }}</span>
                            @endforeach
                        </div>
                        <h2 class="mt-3 font-serif text-xl font-semibold leading-snug text-root-900">
                            <a href="{{ route('research.show', $project) }}" class="hover:underline">{{ $project->title }}</a>
                        </h2>
                        <p class="mt-3 flex-1 text-sm leading-relaxed text-root-700">{{ Str::limit($project->summary, 160) }}</p>
                        <p class="mt-5 text-xs text-root-600">
                            @if ($project->lead && $project->lead->profile_public) Led by {{ $project->lead->name }} · @endif {{ ucfirst($project->status) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @else
            <p class="text-root-600">No public projects yet.</p>
        @endif

        @if ($projects->hasPages())
            <div class="mt-12">{{ $projects->links() }}</div>
        @endif
    </div>
@endsection
