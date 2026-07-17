@php
$invoices = \Modules\InvoiceMaker\Models\Invoice::count();
$paid = \Modules\InvoiceMaker\Models\Invoice::where('status', \Modules\InvoiceMaker\Models\Invoice::STATUS_PAID)->sum('amount_paid') ?: 0;
@endphp
<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <h3 class="font-semibold text-slate-900">{{ __('InvoiceMaker') }}</h3>
    <div class="mt-2 flex gap-4">
        <div>
            <p class="text-3xl font-bold text-slate-900">{{ $invoices }}</p>
            <p class="text-sm text-slate-500">{{ __('dashboard.widget.invoices') }}</p>
        </div>
        <div>
            <p class="text-3xl font-bold text-emerald-600">{{ number_format($paid, 2) }}</p>
            <p class="text-sm text-slate-500">{{ __('dashboard.widget.collected') }}</p>
        </div>
    </div>
    <a href="{{ url('app/invoices/invoices') }}" class="mt-4 inline-flex rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500">{{ __('dashboard.widget.invoices_link') }}</a>
</div>
