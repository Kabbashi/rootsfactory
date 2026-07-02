<x-filament-panels::page>
    @php($board = $this->getBoard())
    @php($me = auth()->id())
    @php($statusColors = ['todo' => 'gray', 'doing' => 'info', 'done' => 'success'])

    <style>
        .rf-board { display: flex; gap: 1rem; overflow-x: auto; padding-bottom: .5rem; align-items: flex-start; }
        .rf-col { flex: 0 0 18rem; width: 18rem; border: 1px solid rgb(229 231 235); border-radius: .75rem; background: rgb(249 250 251); }
        .rf-col__head { display: flex; align-items: center; justify-content: space-between; padding: .75rem 1rem; border-bottom: 1px solid rgb(229 231 235); }
        .rf-col__title { font-weight: 600; color: rgb(55 65 81); }
        .rf-col__body { padding: .75rem; display: flex; flex-direction: column; gap: .75rem; }
        .rf-card { border: 1px solid rgb(229 231 235); border-radius: .5rem; background: #fff; padding: .75rem; box-shadow: 0 1px 2px rgb(0 0 0 / .05); }
        .rf-card__top { display: flex; align-items: flex-start; justify-content: space-between; gap: .5rem; }
        .rf-card__title { font-weight: 500; font-size: .875rem; color: rgb(17 24 39); }
        .rf-card__subject { margin-top: .5rem; font-size: .75rem; }
        .rf-card__subject a { color: rgb(79 70 229); }
        .rf-card__subject a:hover { text-decoration: underline; }
        .rf-meta { margin-top: .75rem; display: flex; flex-direction: column; gap: .25rem; font-size: .75rem; color: rgb(107 114 128); }
        .rf-meta__row { display: flex; justify-content: space-between; gap: .5rem; }
        .rf-meta__val { color: rgb(55 65 81); text-align: right; }
        .rf-tag { margin-top: .5rem; font-size: 11px; }
        .rf-tag--me { color: rgb(217 119 6); }
        .rf-tag--del { color: rgb(2 132 199); }
        .rf-tag--own { color: rgb(156 163 175); }
        .rf-empty { font-size: .75rem; color: rgb(156 163 175); text-align: center; padding: 1.5rem 0; }
        .dark .rf-col { border-color: rgb(55 65 81); background: rgb(255 255 255 / .05); }
        .dark .rf-col__head { border-color: rgb(55 65 81); }
        .dark .rf-col__title { color: rgb(229 231 235); }
        .dark .rf-card { border-color: rgb(55 65 81); background: rgb(17 24 39); }
        .dark .rf-card__title { color: rgb(243 244 246); }
        .dark .rf-card__subject a { color: rgb(129 140 248); }
        .dark .rf-meta__val { color: rgb(209 213 219); }
    </style>

    <p class="fi-ta-empty-state-description" style="font-size:.875rem;color:rgb(107 114 128);">
        Your tasks across the network — assigned to you or delegated by you — grouped by bucket.
        Idea, Concept and Project are the default buckets; add your own with “Add bucket”.
    </p>

    <div class="rf-board">
        @foreach ($board as $column)
            @php($bucket = $column['bucket'])
            <div class="rf-col">
                <div class="rf-col__head">
                    <span class="rf-col__title">{{ $bucket->name }}</span>
                    <x-filament::badge :color="$bucket->isSystem() ? 'primary' : 'gray'">{{ $column['tasks']->count() }}</x-filament::badge>
                </div>

                <div class="rf-col__body">
                    @forelse ($column['tasks'] as $task)
                        <div class="rf-card">
                            <div class="rf-card__top">
                                <span class="rf-card__title">{{ $task->title }}</span>
                                <x-filament::badge :color="$statusColors[$task->status] ?? 'gray'" size="sm">
                                    {{ $task->statusLabel() }}
                                </x-filament::badge>
                            </div>

                            @if ($task->subjectLabel())
                                <div class="rf-card__subject">
                                    @if ($url = $this->subjectUrl($task))
                                        <a href="{{ $url }}">{{ $task->subjectLabel() }}</a>
                                    @else
                                        <span style="color:rgb(107 114 128);">{{ $task->subjectLabel() }}</span>
                                    @endif
                                </div>
                            @endif

                            <dl class="rf-meta">
                                <div class="rf-meta__row"><dt>Assigned to</dt><dd class="rf-meta__val">{{ $task->assignee?->name ?? '—' }}</dd></div>
                                <div class="rf-meta__row"><dt>Given by</dt><dd class="rf-meta__val">{{ $task->creator?->name ?? '—' }}</dd></div>
                                <div class="rf-meta__row"><dt>Due</dt><dd class="rf-meta__val">{{ $task->due_at?->format('d M Y') ?? '—' }}</dd></div>
                                @if ($task->collaborators->isNotEmpty())
                                    <div class="rf-meta__row"><dt>With</dt><dd class="rf-meta__val">{{ $task->collaborators->pluck('name')->join(', ') }}</dd></div>
                                @endif
                            </dl>

                            <div class="rf-tag">
                                @if ($task->assignee_id === $me && $task->created_by === $me)
                                    <span class="rf-tag--own">Personal</span>
                                @elseif ($task->assignee_id === $me)
                                    <span class="rf-tag--me">Assigned to me</span>
                                @elseif ($task->created_by === $me)
                                    <span class="rf-tag--del">Delegated by me</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="rf-empty">No tasks here yet.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
