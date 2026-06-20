@if (session('sso_error'))
    <div class="mb-4 rounded-lg bg-danger-50 p-3 text-sm text-danger-700 dark:bg-danger-400/10 dark:text-danger-400">
        {{ session('sso_error') }}
    </div>
@endif

<div class="relative my-2">
    <div class="absolute inset-0 flex items-center" aria-hidden="true">
        <div class="w-full border-t border-gray-200 dark:border-white/10"></div>
    </div>
    <div class="relative flex justify-center text-xs">
        <span class="bg-white px-2 text-gray-400 dark:bg-gray-900">{{ __('oder') }}</span>
    </div>
</div>

<a
    href="{{ route('sso.conceptnote.redirect') }}"
    class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold outline-none transition duration-75 focus-visible:ring-2 grid w-full bg-emerald-600 text-white hover:bg-emerald-500 focus-visible:ring-emerald-500/50"
>
    Mit conceptnote anmelden
</a>

<p class="mt-2 text-center text-xs text-gray-400">
    Für Mitglieder des conceptnote-Teams „Free-Spirits".
</p>
