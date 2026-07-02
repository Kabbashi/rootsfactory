<x-filament-panels::page>
    <style>
        .rf-eo { display: flex; flex-direction: column; gap: 1.5rem; }
        .rf-eo__lead { font-size: .875rem; color: rgb(107 114 128); }
        .rf-eo__h2 { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: rgb(107 114 128); margin: 0; }
        .rf-eo__grid2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; }
        .rf-eo__cols { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; }
        .rf-eo__panel { border: 1px solid rgb(229 231 235); border-radius: .75rem; padding: 1rem; background: #fff; }
        .rf-eo__col { border: 1px solid rgb(229 231 235); border-radius: .75rem; padding: .75rem; background: #fff; }
        .rf-eo__title { font-size: .875rem; font-weight: 600; color: rgb(31 41 55); margin: 0 0 .5rem; }
        .rf-eo__col-h { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: rgb(107 114 128); margin: 0 0 .5rem; }
        .rf-eo__count { color: rgb(156 163 175); margin-left: .25rem; }
        .rf-eo__list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: .4rem; }
        .rf-eo__row { font-size: .875rem; }
        .rf-eo__link { color: rgb(79 70 229); text-decoration: none; }
        .rf-eo__link:hover { text-decoration: underline; }
        .rf-eo__meta { font-size: .75rem; color: rgb(107 114 128); }
        .rf-eo__card { display: block; border-radius: .5rem; background: rgb(249 250 251); padding: .4rem .55rem; text-decoration: none; color: inherit; }
        .rf-eo__card:hover { background: rgb(243 244 246); }
        .rf-eo__card-title { font-size: .8rem; font-weight: 500; color: rgb(31 41 55); }
        .rf-eo__card-sub { display: block; font-size: .72rem; color: rgb(107 114 128); }
        .rf-eo__muted { font-size: .8rem; color: rgb(156 163 175); }
        .dark .rf-eo__panel, .dark .rf-eo__col { border-color: rgb(55 65 81); background: rgb(31 41 55); }
        .dark .rf-eo__title, .dark .rf-eo__card-title { color: rgb(243 244 246); }
        .dark .rf-eo__link { color: rgb(129 140 248); }
        .dark .rf-eo__card { background: rgb(255 255 255 / .04); }
        .dark .rf-eo__card:hover { background: rgb(255 255 255 / .08); }
    </style>

    <div class="rf-eo">
        <p class="rf-eo__lead">
            The shared workflow: concepts and projects you and others move forward together,
            plus the path from draft to published.
        </p>

        {{-- My work --}}
        @php($mine = $this->getMyWork())
        <div class="rf-eo__grid2">
            <div class="rf-eo__panel">
                <p class="rf-eo__title">My concepts</p>
                <ul class="rf-eo__list">
                    @forelse ($mine['concepts'] as $concept)
                        <li class="rf-eo__row">
                            <a class="rf-eo__link" href="{{ \App\Filament\Resources\ResearchConcepts\ResearchConceptResource::getUrl('edit', ['record' => $concept]) }}">{{ \Illuminate\Support\Str::limit($concept->title, 60) }}</a>
                            <span class="rf-eo__meta">· {{ \App\Models\ResearchConcept::STATUS_LABELS[$concept->status] ?? $concept->status }}</span>
                        </li>
                    @empty
                        <li class="rf-eo__muted">Nothing yet — start one in the Research Hub.</li>
                    @endforelse
                </ul>
            </div>
            <div class="rf-eo__panel">
                <p class="rf-eo__title">My projects</p>
                <ul class="rf-eo__list">
                    @forelse ($mine['projects'] as $project)
                        <li class="rf-eo__row">
                            <a class="rf-eo__link" href="{{ \App\Filament\Resources\ResearchProjects\ResearchProjectResource::getUrl('edit', ['record' => $project]) }}">{{ \Illuminate\Support\Str::limit($project->title, 60) }}</a>
                            <span class="rf-eo__meta">· {{ \App\Models\ResearchProject::STATUSES[$project->status] ?? $project->status }}</span>
                        </li>
                    @empty
                        <li class="rf-eo__muted">You don’t lead or belong to a project yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Research Concepts by stage --}}
        <h2 class="rf-eo__h2">Research Concepts</h2>
        <div class="rf-eo__cols">
            @foreach ($this->getConceptPipeline() as $status => $concepts)
                <div class="rf-eo__col">
                    <p class="rf-eo__col-h">{{ \App\Models\ResearchConcept::STATUS_LABELS[$status] ?? $status }}<span class="rf-eo__count">({{ $concepts->count() }})</span></p>
                    <ul class="rf-eo__list">
                        @forelse ($concepts as $concept)
                            <li>
                                <a class="rf-eo__card" href="{{ \App\Filament\Resources\ResearchConcepts\ResearchConceptResource::getUrl('edit', ['record' => $concept]) }}">
                                    <span class="rf-eo__card-title">{{ \Illuminate\Support\Str::limit($concept->title, 50) }}</span>
                                    <span class="rf-eo__card-sub">{{ $concept->user?->name }}</span>
                                </a>
                            </li>
                        @empty
                            <li class="rf-eo__muted">—</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </div>

        {{-- Research Projects by status --}}
        <h2 class="rf-eo__h2">Research Projects</h2>
        <div class="rf-eo__cols">
            @foreach ($this->getProjectPipeline() as $status => $projects)
                <div class="rf-eo__col">
                    <p class="rf-eo__col-h">{{ \App\Models\ResearchProject::STATUSES[$status] ?? $status }}<span class="rf-eo__count">({{ $projects->count() }})</span></p>
                    <ul class="rf-eo__list">
                        @forelse ($projects as $project)
                            <li>
                                <a class="rf-eo__card" href="{{ \App\Filament\Resources\ResearchProjects\ResearchProjectResource::getUrl('edit', ['record' => $project]) }}">
                                    <span class="rf-eo__card-title">{{ \Illuminate\Support\Str::limit($project->title, 50) }}</span>
                                    <span class="rf-eo__card-sub">{{ $project->lead?->name }}</span>
                                </a>
                            </li>
                        @empty
                            <li class="rf-eo__muted">—</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </div>

        {{-- Publications pipeline by stage --}}
        <h2 class="rf-eo__h2">Publications</h2>
        <div class="rf-eo__cols">
            @foreach ($this->getPipeline() as $stage => $publications)
                <div class="rf-eo__col">
                    <p class="rf-eo__col-h">{{ \App\Models\Publication::STATUSES[$stage] ?? $stage }}<span class="rf-eo__count">({{ $publications->count() }})</span></p>
                    <ul class="rf-eo__list">
                        @forelse ($publications as $publication)
                            <li>
                                <a class="rf-eo__card" href="{{ \App\Filament\Resources\Publications\PublicationResource::getUrl('edit', ['record' => $publication]) }}">
                                    <span class="rf-eo__card-title">{{ \Illuminate\Support\Str::limit($publication->title, 50) }}</span>
                                    <span class="rf-eo__card-sub">{{ $publication->typeLabel() }}</span>
                                </a>
                            </li>
                        @empty
                            <li class="rf-eo__muted">—</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </div>

        {{-- My reviews --}}
        <div class="rf-eo__panel">
            <p class="rf-eo__title">Reviews assigned to me</p>
            <ul class="rf-eo__list">
                @forelse ($this->getMyReviews() as $review)
                    <li class="rf-eo__row">
                        <a class="rf-eo__link" href="{{ \App\Filament\Resources\Publications\PublicationResource::getUrl('edit', ['record' => $review->publication]) }}">
                            {{ $review->publication?->title ?? 'Publication' }}
                        </a>
                        <span class="rf-eo__meta">
                            — {{ \App\Models\Review::STAGES[$review->stage] ?? $review->stage }},
                            {{ \App\Models\Review::STATUSES[$review->status] ?? $review->status }}
                            @if ($review->due_at) · due {{ $review->due_at->toFormattedDateString() }} @endif
                        </span>
                    </li>
                @empty
                    <li class="rf-eo__muted">No reviews assigned to you.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-filament-panels::page>
