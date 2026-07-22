@extends('layouts.shell', ['title' => __('New Vision')])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ __('New Vision') }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        
@if (session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 mb-6">{{ session('success') }}</div>
@endif

        <form method="POST" action="{{ route('visionflow.organizations.visions.store', $organization) }}" class="space-y-4">
            @csrf
            <div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Content') }}</label>
    <textarea name="content" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>{{ '' }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
    <select name="status" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
        <option value="drafting" {{ null == "drafting" ? "selected" : "" }}>{{ __('drafting') }}</option><option value="reviewing" {{ null == "reviewing" ? "selected" : "" }}>{{ __('reviewing') }}</option><option value="approved" {{ null == "approved" ? "selected" : "" }}>{{ __('approved') }}</option>
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Version') }}</label>
    <input type="number" name="version" value="''" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
</div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('visionflow.organizations.visions.index', $organization) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
