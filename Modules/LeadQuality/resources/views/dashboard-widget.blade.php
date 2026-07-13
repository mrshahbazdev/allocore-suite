<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-sm font-medium text-slate-500">{{ __('LeadOS') }}</div>
            <div class="mt-1 text-lg font-semibold text-slate-900">{{ __('Lead generation snapshot') }}</div>
        </div>
        <a href="{{ route('leadquality.dashboard') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Open') }}</a>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-slate-500">{{ __('Leads') }}</div>
            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $snapshot['total_leads'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-slate-500">{{ __('Good leads') }}</div>
            <div class="mt-1 text-2xl font-semibold text-emerald-600">{{ $snapshot['good_leads'] ?? 0 }}</div>
        </div>
    </div>
</div>
