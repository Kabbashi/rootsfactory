@extends('public.layout')

@section('title', $project->title)
@section('description', Str::limit(strip_tags($project->summary ?? ''), 155))

@section('content')
    <article class="mx-auto max-w-3xl px-6 py-14">
        <a href="{{ route('research.index') }}" class="text-sm font-medium text-root-600 hover:text-root-900">← All research</a>

        <header class="mt-6 border-b border-root-100 pb-8">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-root-800 px-3 py-1 text-xs font-semibold text-root-50">{{ $project->kindLabel() }}</span>
                <span class="text-xs uppercase tracking-wide text-root-600">{{ ucfirst($project->status) }}</span>
            </div>
            <h1 class="mt-3 font-serif text-4xl font-bold leading-tight text-root-900">{{ $project->title }}</h1>
            @if ($project->summary)
                <p class="mt-4 text-root-700">{{ $project->summary }}</p>
            @endif
            <p class="mt-4 text-sm text-root-600">
                @if ($project->lead && $project->lead->profile_public) Led by {{ $project->lead->name }} @endif
                @if ($project->start_date) · {{ $project->start_date->format('M Y') }}@if ($project->end_date) – {{ $project->end_date->format('M Y') }} @endif @endif
            </p>
        </header>

        <dl class="mt-8 space-y-6">
            @foreach ([
                'Objectives' => $project->objectives,
                'Research questions' => $project->research_questions,
                'Methodology' => $project->methodology,
                'Data collection' => $project->data_collection,
                'Findings' => $project->findings,
            ] as $label => $value)
                @if (filled($value))
                    <div>
                        <dt class="text-sm font-semibold uppercase tracking-wide text-root-600">{{ $label }}</dt>
                        <dd class="prose mt-2 max-w-none">{!! \Illuminate\Support\Str::markdown($value) !!}</dd>
                    </div>
                @endif
            @endforeach
        </dl>

        @if ($project->regions->isNotEmpty() || $project->topics->isNotEmpty())
            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ($project->topics as $topic)
                    <span class="rounded-full bg-root-100 px-3 py-1 text-xs text-root-700">{{ $topic->name }}</span>
                @endforeach
                @foreach ($project->regions as $region)
                    <span class="rounded-full bg-root-100 px-3 py-1 text-xs text-root-700">{{ $region->name }}</span>
                @endforeach
            </div>
        @endif

        @php($publicTeam = $project->members->where('profile_public', true))
        @if ($publicTeam->isNotEmpty())
            <div class="mt-8">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-root-600">Research team</h2>
                <ul class="mt-2 flex flex-wrap gap-2 text-sm text-root-700">
                    @foreach ($publicTeam as $member)
                        <li>@if ($member->isPublicAuthor())<a href="{{ route('people.show', $member) }}" class="hover:underline">{{ $member->name }}</a>@else{{ $member->name }}@endif@if (! $loop->last),@endif</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($publications->isNotEmpty())
            <div class="mt-10 border-t border-root-100 pt-6">
                <h2 class="font-serif text-2xl font-semibold text-root-900">Publications</h2>
                <ul class="mt-4 space-y-3">
                    @foreach ($publications as $publication)
                        <li>
                            <a href="{{ route('publications.show', $publication) }}" class="text-root-800 hover:underline">{{ $publication->title }}</a>
                            <span class="text-xs text-root-600">— {{ $publication->typeLabel() }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </article>
@endsection
