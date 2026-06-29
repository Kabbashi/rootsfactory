<x-filament-panels::page>
    <p class="rf-portal__lead">
        A shared workshop for collaborative research, qualitative analysis and joint
        scientific writing. Choose where you want to work.
    </p>

    <div class="rf-portal__grid">
        @foreach ($this->getCenters() as $center)
            <a href="{{ $center['url'] }}" class="rf-center">
                <span class="rf-center__icon">
                    @svg($center['icon'], 'rf-center__svg')
                </span>
                <span class="rf-center__body">
                    <span class="rf-center__name">{{ $center['name'] }}</span>
                    <span class="rf-center__desc">{{ $center['description'] }}</span>
                </span>
            </a>
        @endforeach
    </div>

    <style>
        .rf-portal__lead {
            color: rgb(107 114 128);
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            max-width: 48rem;
        }
        .dark .rf-portal__lead { color: rgb(156 163 175); }
        .rf-portal__grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .rf-portal__grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .rf-portal__grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        .rf-center {
            display: flex;
            gap: 0.875rem;
            align-items: flex-start;
            padding: 1.1rem;
            border-radius: 0.75rem;
            border: 1px solid rgb(229 231 235);
            background: rgb(255 255 255);
            text-decoration: none;
            transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
        }
        .dark .rf-center {
            border-color: rgb(55 65 81);
            background: rgb(31 41 55);
        }
        .rf-center:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,.06);
            transform: translateY(-2px);
            border-color: rgb(99 102 241);
        }
        .rf-center__icon {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.625rem;
            background: rgb(238 242 255);
            color: rgb(79 70 229);
        }
        .dark .rf-center__icon { background: rgb(55 65 81); color: rgb(165 180 252); }
        .rf-center__svg { width: 1.4rem; height: 1.4rem; }
        .rf-center__body { display: flex; flex-direction: column; gap: 0.25rem; }
        .rf-center__name { font-weight: 600; color: rgb(17 24 39); }
        .dark .rf-center__name { color: rgb(243 244 246); }
        .rf-center__desc { font-size: 0.825rem; color: rgb(107 114 128); line-height: 1.35; }
        .dark .rf-center__desc { color: rgb(156 163 175); }
    </style>
</x-filament-panels::page>
