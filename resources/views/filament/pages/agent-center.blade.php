<x-filament-panels::page>
    <p class="rf-agent__lead">
        Think out loud with Roots Factory AI. Ask a question, sketch an argument, or draft something —
        then keep the reply as a draft idea if it's worth it. Nothing is published automatically.
    </p>

    <div class="rf-agent__box">
        <textarea
            wire:model="prompt"
            rows="5"
            class="rf-agent__input"
            placeholder="e.g. What are the trade-offs of pooled funds vs. direct grants for smallholder farmers?"
        ></textarea>

        <div class="rf-agent__actions">
            <button type="button" wire:click="think" wire:loading.attr="disabled" class="rf-btn rf-btn--primary">
                <span wire:loading.remove wire:target="think">Think with AI</span>
                <span wire:loading wire:target="think">Thinking…</span>
            </button>
        </div>
    </div>

    @if (filled($answer))
        <div class="rf-agent__answer">
            <div class="rf-agent__answer-head">
                <span class="rf-agent__badge">Roots Factory AI</span>
                <button type="button" wire:click="saveAsIdea" class="rf-btn rf-btn--ghost">
                    Save as draft idea
                </button>
            </div>
            <div class="rf-prose">
                {!! \Illuminate\Support\Str::markdown($answer) !!}
            </div>
        </div>
    @endif

    <style>
        .rf-agent__lead { color: rgb(107 114 128); font-size: .95rem; margin-bottom: 1rem; }
        .dark .rf-agent__lead { color: rgb(156 163 175); }
        .rf-agent__box {
            border: 1px solid rgb(229 231 235); border-radius: .75rem; padding: 1rem; background: #fff;
        }
        .dark .rf-agent__box { border-color: rgb(55 65 81); background: rgb(31 41 55); }
        .rf-agent__input {
            width: 100%; border: 1px solid rgb(209 213 219); border-radius: .5rem; padding: .75rem;
            font-size: .9rem; resize: vertical; background: transparent; color: inherit;
        }
        .dark .rf-agent__input { border-color: rgb(75 85 99); }
        .rf-agent__input:focus { outline: 2px solid rgb(16 185 129); outline-offset: 1px; }
        .rf-agent__actions { margin-top: .75rem; display: flex; gap: .5rem; }
        .rf-btn {
            border-radius: .5rem; padding: .5rem .9rem; font-size: .85rem; font-weight: 600; cursor: pointer;
            border: 1px solid transparent;
        }
        .rf-btn--primary { background: rgb(16 185 129); color: #fff; }
        .rf-btn--primary:hover { background: rgb(5 150 105); }
        .rf-btn--primary:disabled { opacity: .6; cursor: default; }
        .rf-btn--ghost { background: transparent; border-color: rgb(209 213 219); color: inherit; }
        .rf-btn--ghost:hover { border-color: rgb(16 185 129); }
        .rf-agent__answer {
            margin-top: 1.25rem; border: 1px solid rgb(229 231 235); border-radius: .75rem; padding: 1.1rem; background: #fff;
        }
        .dark .rf-agent__answer { border-color: rgb(55 65 81); background: rgb(31 41 55); }
        .rf-agent__answer-head {
            display: flex; align-items: center; justify-content: space-between; margin-bottom: .75rem;
        }
        .rf-agent__badge {
            font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
            color: rgb(5 150 105); background: rgb(209 250 229); border-radius: 999px; padding: .15rem .6rem;
        }
        .dark .rf-agent__badge { background: rgb(6 78 59); color: rgb(167 243 208); }
        .rf-prose { font-size: .9rem; line-height: 1.6; }
        .rf-prose h2 { font-size: 1.05rem; font-weight: 700; margin: 1rem 0 .4rem; }
        .rf-prose h3 { font-size: .95rem; font-weight: 700; margin: .8rem 0 .3rem; }
        .rf-prose ul { list-style: disc; padding-left: 1.25rem; margin: .5rem 0; }
        .rf-prose ol { list-style: decimal; padding-left: 1.25rem; margin: .5rem 0; }
        .rf-prose p { margin: .5rem 0; }
        .rf-prose strong { font-weight: 700; }
        .rf-prose blockquote { border-left: 3px solid rgb(16 185 129); padding-left: .75rem; color: rgb(107 114 128); }
    </style>
</x-filament-panels::page>
