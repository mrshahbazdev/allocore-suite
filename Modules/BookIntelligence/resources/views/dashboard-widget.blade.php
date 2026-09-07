<a href="{{ route('bookintelligence.dashboard') }}" class="group block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-orange-200 hover:shadow-md">
    <div class="flex items-start justify-between gap-4">
        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-50 text-[#ff9200]">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.966 8.966 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25A8.966 8.966 0 0118 3.75c1.052 0 2.062.18 3 .512v14.25A8.966 8.966 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
            </svg>
        </span>
        <span class="text-sm font-semibold text-[#0094af] group-hover:underline">{{ __('Open') }}</span>
    </div>
    <h2 class="mt-4 font-bold text-slate-900">{{ __('Knowledge Library') }}</h2>
    <p class="mt-1 text-sm text-slate-500">{{ trans_choice(':count book|:count books', $bookCount, ['count' => $bookCount]) }} · {{ $analysisCount }} {{ __('AI-ready') }} · {{ $readingCount }} {{ __('currently reading') }}</p>
</a>
