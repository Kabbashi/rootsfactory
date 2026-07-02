@php($member = $getRecord())
@php(
    $chips = function ($items) {
        return collect($items ?? [])->filter()->values();
    }
)

<div class="space-y-3">
    <div>
        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $member->name }}</p>
        @if ($member->title)
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $member->title }}</p>
        @endif
    </div>

    @if ($member->bio)
        <p class="text-sm text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($member->bio, 180) }}</p>
    @endif

    @foreach ([
        'Expertise' => $member->expertise,
        'Country experience' => $member->country_experience,
        'Languages' => $member->languages,
        'Methods' => $member->method_competencies,
    ] as $label => $values)
        @if ($chips($values)->isNotEmpty())
            <div>
                <p class="text-[11px] uppercase tracking-wide text-gray-400">{{ $label }}</p>
                <div class="mt-1 flex flex-wrap gap-1">
                    @foreach ($chips($values) as $value)
                        <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-white/10 px-2 py-0.5 text-xs text-gray-700 dark:text-gray-200">{{ $value }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    @php(
        $links = collect()
            ->when($member->linkedin, fn ($c) => $c->push(['label' => 'LinkedIn', 'url' => $member->linkedin]))
            ->when($member->instagram, fn ($c) => $c->push(['label' => 'Instagram', 'url' => $member->instagram]))
            ->merge(collect($member->links ?? [])->filter(fn ($l) => ! empty($l['url'])))
    )
    @if ($links->isNotEmpty())
        <div class="flex flex-wrap gap-2 pt-1">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1 text-xs text-primary-600 hover:underline dark:text-primary-400">
                    @svg('heroicon-m-link', 'h-3 w-3')
                    {{ $link['label'] ?: $link['url'] }}
                </a>
            @endforeach
        </div>
    @endif
</div>
