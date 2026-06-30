<x-filament-panels::page>
    <input
        type="search"
        wire:model.live.debounce.500ms="q"
        placeholder="Search ideas, concepts, projects, the library and publications…"
        autofocus
        class="rf-search__input"
    />

    @php($results = $this->getResults())

    @if (strlen(trim($q)) < 2)
        <p class="rf-search__hint">Type at least two characters to search.</p>
    @elseif (empty($results))
        <p class="rf-search__hint">No matches for “{{ $q }}”.</p>
    @else
        <div class="rf-search__groups">
            @foreach ($results as $group)
                <div>
                    <h3 class="rf-search__heading">{{ $group['label'] }} ({{ count($group['items']) }})</h3>
                    <ul class="rf-search__list">
                        @foreach ($group['items'] as $item)
                            <li>
                                <a href="{{ $item['url'] }}" class="rf-search__item">
                                    <span class="rf-search__title">{{ $item['title'] }}</span>
                                    @if ($item['snippet'])
                                        <span class="rf-search__snippet">{{ $item['snippet'] }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif

    <style>
        .rf-search__input {
            width: 100%;
            border: 1px solid rgb(209 213 219);
            border-radius: 0.75rem;
            background: #fff;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        .dark .rf-search__input { background: rgb(17 24 39); border-color: rgb(55 65 81); color: #fff; }
        .rf-search__input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.2); }
        .rf-search__hint { margin-top: 1rem; font-size: .875rem; color: rgb(107 114 128); }
        .rf-search__groups { margin-top: 1.25rem; display: flex; flex-direction: column; gap: 1.5rem; }
        .rf-search__heading {
            font-size: .72rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: .05em; color: rgb(107 114 128); margin-bottom: .5rem;
        }
        .rf-search__list {
            border: 1px solid rgb(229 231 235); border-radius: 0.75rem; overflow: hidden; background: #fff;
        }
        .dark .rf-search__list { border-color: rgb(55 65 81); background: rgb(31 41 55); }
        .rf-search__list > li + li { border-top: 1px solid rgb(243 244 246); }
        .dark .rf-search__list > li + li { border-top-color: rgb(55 65 81); }
        .rf-search__item { display: block; padding: .75rem 1rem; text-decoration: none; transition: background .12s ease; }
        .rf-search__item:hover { background: rgb(249 250 251); }
        .dark .rf-search__item:hover { background: rgb(55 65 81); }
        .rf-search__title { font-weight: 600; color: #4f46e5; }
        .rf-search__snippet { display: block; margin-top: .15rem; font-size: .875rem; color: rgb(107 114 128); }
    </style>
</x-filament-panels::page>
