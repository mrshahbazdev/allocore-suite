@php
$count = \Modules\AuditPro\Models\Audit::count();
@endphp
<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <h3 class="font-semibold text-slate-900">{{ __('AuditPro') }}</h3>
    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $count }}</p>
    <p class="text-sm text-slate-500">{{ __('dashboard.widget.audits') }}</p>
    <a href="{{ url('app/audit/audits') }}" class="mt-4 inline-flex rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500">{{ __('dashboard.widget.view_audits') }}</a>
</div>
