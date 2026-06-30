<x-filament-panels::page>
    <p class="text-sm text-gray-500 dark:text-gray-400">
        The shared workflow: concepts and projects you and others move forward together,
        plus the path from draft to published.
    </p>

    {{-- My work --}}
    @php($mine = $this->getMyWork())
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold">My concepts</h3>
            <ul class="mt-2 space-y-1">
                @forelse ($mine['concepts'] as $concept)
                    <li class="text-sm">
                        <a href="{{ \App\Filament\Resources\ResearchConcepts\ResearchConceptResource::getUrl('edit', ['record' => $concept]) }}"
                           class="text-primary-600 hover:underline">{{ \Illuminate\Support\Str::limit($concept->title, 60) }}</a>
                        <span class="text-xs text-gray-500">· {{ \App\Models\ResearchConcept::STATUS_LABELS[$concept->status] ?? $concept->status }}</span>
                    </li>
                @empty
                    <li class="text-sm text-gray-400">Nothing yet — start one in the Research Hub.</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold">My projects</h3>
            <ul class="mt-2 space-y-1">
                @forelse ($mine['projects'] as $project)
                    <li class="text-sm">
                        <a href="{{ \App\Filament\Resources\ResearchProjects\ResearchProjectResource::getUrl('edit', ['record' => $project]) }}"
                           class="text-primary-600 hover:underline">{{ \Illuminate\Support\Str::limit($project->title, 60) }}</a>
                        <span class="text-xs text-gray-500">· {{ \App\Models\ResearchProject::STATUSES[$project->status] ?? $project->status }}</span>
                    </li>
                @empty
                    <li class="text-sm text-gray-400">You don’t lead or belong to a project yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Research Concepts by stage --}}
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Research Concepts</h2>
    <div class="grid gap-4 sm:grid-cols-3">
        @foreach ($this->getConceptPipeline() as $status => $concepts)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    {{ \App\Models\ResearchConcept::STATUS_LABELS[$status] ?? $status }}
                    <span class="ml-1 text-gray-400">({{ $concepts->count() }})</span>
                </h3>
                <ul class="mt-2 space-y-2">
                    @forelse ($concepts as $concept)
                        <li>
                            <a href="{{ \App\Filament\Resources\ResearchConcepts\ResearchConceptResource::getUrl('edit', ['record' => $concept]) }}"
                               class="block rounded-lg bg-gray-50 dark:bg-gray-800 px-2 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                <span class="font-medium">{{ \Illuminate\Support\Str::limit($concept->title, 50) }}</span>
                                <span class="block text-xs text-gray-500">{{ $concept->user?->name }}</span>
                            </a>
                        </li>
                    @empty
                        <li class="text-xs text-gray-400">—</li>
                    @endforelse
                </ul>
            </div>
        @endforeach
    </div>

    {{-- Research Projects by status --}}
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Research Projects</h2>
    <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
        @foreach ($this->getProjectPipeline() as $status => $projects)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    {{ \App\Models\ResearchProject::STATUSES[$status] ?? $status }}
                    <span class="ml-1 text-gray-400">({{ $projects->count() }})</span>
                </h3>
                <ul class="mt-2 space-y-2">
                    @forelse ($projects as $project)
                        <li>
                            <a href="{{ \App\Filament\Resources\ResearchProjects\ResearchProjectResource::getUrl('edit', ['record' => $project]) }}"
                               class="block rounded-lg bg-gray-50 dark:bg-gray-800 px-2 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                <span class="font-medium">{{ \Illuminate\Support\Str::limit($project->title, 50) }}</span>
                                <span class="block text-xs text-gray-500">{{ $project->lead?->name }}</span>
                            </a>
                        </li>
                    @empty
                        <li class="text-xs text-gray-400">—</li>
                    @endforelse
                </ul>
            </div>
        @endforeach
    </div>

    {{-- Publications pipeline by stage --}}
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Publications</h2>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @foreach ($this->getPipeline() as $stage => $publications)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    {{ \App\Models\Publication::STATUSES[$stage] ?? $stage }}
                    <span class="ml-1 text-gray-400">({{ $publications->count() }})</span>
                </h3>
                <ul class="mt-2 space-y-2">
                    @forelse ($publications as $publication)
                        <li>
                            <a href="{{ \App\Filament\Resources\Publications\PublicationResource::getUrl('edit', ['record' => $publication]) }}"
                               class="block rounded-lg bg-gray-50 dark:bg-gray-800 px-2 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                <span class="font-medium">{{ \Illuminate\Support\Str::limit($publication->title, 50) }}</span>
                                <span class="block text-xs text-gray-500">{{ $publication->typeLabel() }}</span>
                            </a>
                        </li>
                    @empty
                        <li class="text-xs text-gray-400">—</li>
                    @endforelse
                </ul>
            </div>
        @endforeach
    </div>

    {{-- My reviews --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <h3 class="text-sm font-semibold">Reviews assigned to me</h3>
        <ul class="mt-2 space-y-1">
            @forelse ($this->getMyReviews() as $review)
                <li class="text-sm">
                    <a href="{{ \App\Filament\Resources\Publications\PublicationResource::getUrl('edit', ['record' => $review->publication]) }}"
                       class="text-primary-600 hover:underline">
                        {{ $review->publication?->title ?? 'Publication' }}
                    </a>
                    <span class="text-gray-500">
                        — {{ \App\Models\Review::STAGES[$review->stage] ?? $review->stage }},
                        {{ \App\Models\Review::STATUSES[$review->status] ?? $review->status }}
                        @if ($review->due_at) · due {{ $review->due_at->toFormattedDateString() }} @endif
                    </span>
                </li>
            @empty
                <li class="text-sm text-gray-400">No reviews assigned to you.</li>
            @endforelse
        </ul>
    </div>
</x-filament-panels::page>
