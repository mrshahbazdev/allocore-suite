@php
$companies = \Modules\FinancialPlatform\Models\Company::count();
$leads = \Modules\FinancialPlatform\Models\Lead::count();
@endphp
<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <h3 class="font-semibold text-slate-900">{{ __('Financial Platform') }}</h3>
    <div class="mt-2 flex gap-4">
        <div>
            <p class="text-3xl font-bold text-slate-900">{{ $companies }}</p>
            <p class="text-sm text-slate-500">{{ __('dashboard.widget.companies') }}</p>
        </div>
        <div>
            <p class="text-3xl font-bold text-slate-900">{{ $leads }}</p>
            <p class="text-sm text-slate-500">{{ __('dashboard.widget.leads') }}</p>
        </div>
    </div>
    <a href="{{ url('app/finance/companies') }}" class="mt-4 inline-flex rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500">{{ __('dashboard.widget.companies_link') }}</a>
</div>
