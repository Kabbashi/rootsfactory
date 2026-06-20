<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Latest discussion
        </x-slot>

        <x-slot name="description">
            Recent comments across ideas and topics
        </x-slot>

        @php($comments = $this->getComments())

        @if ($comments->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">
                No discussion yet — open an idea or topic and start the conversation.
            </p>
        @else
            <ul role="list" class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($comments as $comment)
                    <li class="flex items-start gap-3 py-3">
                        <div class="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-500/20 dark:text-primary-300">
                            {{ \Illuminate\Support\Str::of($comment->author)->substr(0, 1)->upper() }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm">
                                <span class="font-medium text-gray-950 dark:text-white">{{ $comment->author }}</span>
                                @if ($comment->isReply)
                                    <span class="text-xs text-gray-400">replied</span>
                                @endif
                                <span class="text-gray-300 dark:text-gray-600">·</span>
                                <span class="text-xs text-gray-400">{{ $comment->when }}</span>
                            </p>

                            <p class="mt-0.5 truncate text-sm text-gray-600 dark:text-gray-300">{{ $comment->body }}</p>

                            @if ($comment->subjectUrl)
                                <a href="{{ $comment->subjectUrl }}" class="mt-1 inline-flex text-xs font-medium text-primary-600 hover:underline dark:text-primary-400">
                                    on &ldquo;{{ $comment->subjectLabel }}&rdquo;
                                </a>
                            @else
                                <span class="mt-1 inline-flex text-xs text-gray-400">on {{ $comment->subjectLabel }}</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
