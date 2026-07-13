<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="text-sm font-medium text-slate-500">{{ __('FinancialPlatform') }}</div>
            <h3 class="mt-1 text-lg font-semibold text-slate-900">{{ __('Corporate Maturity') }}</h3>
        </div>
        <a href="{{ url('app/finance') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Open') }}</a>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-xs text-slate-500">{{ __('Companies') }}</div>
            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $companies }}</div>
        </div>
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-xs text-slate-500">{{ __('Analyses') }}</div>
            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $analyses }}</div>
        </div>
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-xs text-slate-500">{{ __('Leads') }}</div>
            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $leads }}</div>
        </div>
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-xs text-slate-500">{{ __('Revenue') }}</div>
            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($revenue, 0) }}</div>
        </div>
    </div>

    <div class="mt-4 rounded-xl bg-indigo-50 px-4 py-3">
        <div class="text-xs font-medium uppercase tracking-wide text-indigo-600">{{ __('Corporate Maturity') }}</div>
        <div class="mt-1 text-2xl font-semibold text-indigo-900">{{ number_format($maturity, 1) }}/100</div>
    </div>
</div>
