@php($member = $getRecord())
@php($chips = fn ($items) => collect($items ?? [])->filter()->values())
@php(
    $links = collect()
        ->when($member->linkedin, fn ($c) => $c->push(['label' => 'LinkedIn', 'url' => $member->linkedin]))
        ->when($member->instagram, fn ($c) => $c->push(['label' => 'Instagram', 'url' => $member->instagram]))
        ->merge(collect($member->links ?? [])->filter(fn ($l) => ! empty($l['url'])))
)

<style>
    .rf-mc { display: flex; flex-direction: column; gap: .75rem; }
    .rf-mc__name { font-size: 1rem; font-weight: 600; color: rgb(17 24 39); }
    .rf-mc__title { font-size: .875rem; color: rgb(107 114 128); }
    .rf-mc__bio { font-size: .875rem; color: rgb(75 85 99); }
    .rf-mc__label { font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: rgb(156 163 175); }
    .rf-mc__chips { margin-top: .25rem; display: flex; flex-wrap: wrap; gap: .25rem; }
    .rf-mc__chip { display: inline-flex; align-items: center; border-radius: .375rem; background: rgb(243 244 246); padding: .125rem .5rem; font-size: .75rem; color: rgb(55 65 81); }
    .rf-mc__links { display: flex; flex-wrap: wrap; gap: .5rem; padding-top: .25rem; }
    .rf-mc__link { display: inline-flex; align-items: center; gap: .25rem; font-size: .75rem; color: rgb(79 70 229); }
    .rf-mc__link:hover { text-decoration: underline; }
    .dark .rf-mc__name { color: rgb(243 244 246); }
    .dark .rf-mc__bio { color: rgb(209 213 219); }
    .dark .rf-mc__chip { background: rgb(255 255 255 / .1); color: rgb(229 231 235); }
    .dark .rf-mc__link { color: rgb(129 140 248); }
</style>

<div class="rf-mc">
    <div>
        <p class="rf-mc__name">{{ $member->name }}</p>
        @if ($member->title)
            <p class="rf-mc__title">{{ $member->title }}</p>
        @endif
    </div>

    @if ($member->bio)
        <p class="rf-mc__bio">{{ \Illuminate\Support\Str::limit($member->bio, 180) }}</p>
    @endif

    @foreach ([
        'Expertise' => $member->expertise,
        'Country experience' => $member->country_experience,
        'Languages' => $member->languages,
        'Methods' => $member->method_competencies,
    ] as $label => $values)
        @if ($chips($values)->isNotEmpty())
            <div>
                <p class="rf-mc__label">{{ $label }}</p>
                <div class="rf-mc__chips">
                    @foreach ($chips($values) as $value)
                        <span class="rf-mc__chip">{{ $value }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    @if ($links->isNotEmpty())
        <div class="rf-mc__links">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="rf-mc__link">
                    @svg('heroicon-m-link', '', ['style' => 'width:.75rem;height:.75rem;'])
                    {{ $link['label'] ?: $link['url'] }}
                </a>
            @endforeach
        </div>
    @endif
</div>
