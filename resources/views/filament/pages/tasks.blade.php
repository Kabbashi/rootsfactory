<x-filament-panels::page>
    @php($board = $this->getBoard())
    @php($me = auth()->id())
    @php($statusColors = ['todo' => 'gray', 'doing' => 'info', 'done' => 'success'])
    @php($bucketColors = ['idea' => 'warning', 'concept' => 'info', 'project' => 'success'])

    <p class="text-sm text-gray-500 dark:text-gray-400">
        Your tasks across the network — assigned to you or delegated by you — grouped by what they are about.
    </p>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        @foreach (\App\Models\Task::BUCKETS as $key => $label)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-white/5">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $label }}</span>
                    <x-filament::badge :color="$bucketColors[$key] ?? 'gray'">{{ $board[$key]->count() }}</x-filament::badge>
                </div>

                <div class="p-3 space-y-3">
                    @forelse ($board[$key] as $task)
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3 shadow-sm">
                            <div class="flex items-start justify-between gap-2">
                                <span class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $task->title }}</span>
                                <x-filament::badge :color="$statusColors[$task->status] ?? 'gray'" size="sm">
                                    {{ $task->statusLabel() }}
                                </x-filament::badge>
                            </div>

                            @if ($task->subjectLabel())
                                <div class="mt-2">
                                    @if ($url = $this->subjectUrl($task))
                                        <a href="{{ $url }}" class="text-xs text-primary-600 hover:underline dark:text-primary-400">
                                            {{ $task->subjectLabel() }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-500">{{ $task->subjectLabel() }}</span>
                                    @endif
                                </div>
                            @endif

                            <dl class="mt-3 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                                <div class="flex justify-between gap-2">
                                    <dt>Assigned to</dt>
                                    <dd class="text-gray-700 dark:text-gray-300">{{ $task->assignee?->name ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between gap-2">
                                    <dt>Given by</dt>
                                    <dd class="text-gray-700 dark:text-gray-300">{{ $task->creator?->name ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between gap-2">
                                    <dt>Due</dt>
                                    <dd class="text-gray-700 dark:text-gray-300">{{ $task->due_at?->format('d M Y') ?? '—' }}</dd>
                                </div>
                                @if ($task->collaborators->isNotEmpty())
                                    <div class="flex justify-between gap-2">
                                        <dt>With</dt>
                                        <dd class="text-gray-700 dark:text-gray-300 text-right">{{ $task->collaborators->pluck('name')->join(', ') }}</dd>
                                    </div>
                                @endif
                            </dl>

                            <div class="mt-2">
                                @if ($task->assignee_id === $me && $task->created_by === $me)
                                    <span class="text-[11px] text-gray-400">Personal</span>
                                @elseif ($task->assignee_id === $me)
                                    <span class="text-[11px] text-amber-600 dark:text-amber-400">Assigned to me</span>
                                @elseif ($task->created_by === $me)
                                    <span class="text-[11px] text-sky-600 dark:text-sky-400">Delegated by me</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 text-center py-6">No tasks here yet.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
