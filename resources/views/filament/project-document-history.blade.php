<div class="space-y-4">
    @forelse ($versions as $version)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3">
            <div class="flex items-center justify-between text-sm">
                <span class="font-medium">Version {{ $version->version_no }}</span>
                <span class="text-gray-500">
                    {{ $version->author?->name ?? '—' }} · {{ $version->created_at?->diffForHumans() }}
                </span>
            </div>
            @if (filled($version->changelog))
                <p class="mt-1 text-sm text-gray-500">{{ $version->changelog }}</p>
            @endif
            <pre class="mt-2 whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($version->body, 600) }}</pre>
        </div>
    @empty
        <p class="text-sm text-gray-500">No versions yet.</p>
    @endforelse
</div>
