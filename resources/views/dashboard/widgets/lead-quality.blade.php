@php
$contacts = \Modules\LeadQuality\Models\Contact::count();
@endphp
<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <h3 class="font-semibold text-slate-900">{{ __('LeadOS') }}</h3>
    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $contacts }}</p>
    <p class="text-sm text-slate-500">{{ __('dashboard.widget.contacts') }}</p>
    <a href="{{ url('app/leads/contacts') }}" class="mt-4 inline-flex rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500">{{ __('dashboard.widget.contacts_link') }}</a>
</div>
