@if ($cookieConsent !== 'all')
    <div x-data="{ open: {{ $cookieConsent === null ? 'true' : 'false' }} }" x-show="open" x-transition class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-200 bg-white p-4 shadow-lg">
        <div class="mx-auto flex max-w-5xl flex-col items-center justify-between gap-3 sm:flex-row">
            <p class="text-sm text-slate-600">
                {{ __('We use cookies to improve your experience. You can accept all or only necessary cookies.') }}
            </p>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('cookie-consent.store') }}">
                    @csrf
                    <input type="hidden" name="consent" value="necessary">
                    <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Necessary only') }}</button>
                </form>
                <form method="POST" action="{{ route('cookie-consent.store') }}">
                    @csrf
                    <input type="hidden" name="consent" value="all">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">{{ __('Accept all') }}</button>
                </form>
            </div>
        </div>
    </div>
@endif
