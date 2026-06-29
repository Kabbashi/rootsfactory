<div class="space-y-3">
    @if (filled($version->changelog))
        <p class="text-sm text-gray-500">{{ $version->changelog }}</p>
    @endif
    @if (filled($version->abstract))
        <div>
            <h4 class="text-sm font-semibold">Abstract</h4>
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $version->abstract }}</p>
        </div>
    @endif
    <div>
        <h4 class="text-sm font-semibold">Body</h4>
        <pre class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300">{{ $version->body }}</pre>
    </div>
</div>
