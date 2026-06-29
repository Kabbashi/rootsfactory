<x-filament-panels::page>
    <p class="text-sm text-gray-500 dark:text-gray-400">
        The path from draft to published. Open a manuscript to move it to the next stage,
        assign reviewers, and snapshot versions.
    </p>

    {{-- Pipeline by stage --}}
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
