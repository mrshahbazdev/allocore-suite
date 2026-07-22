@extends('layouts.shell', ['title' => __('New Mission')])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ __('New Mission') }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        
@if (session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 mb-6">{{ session('success') }}</div>
@endif

        <form method="POST" action="{{ route('visionflow.organizations.missions.store', $organization) }}" class="space-y-4">
            @csrf
            <div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Vision') }}</label>
    <select name="vision_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
        <option value="">—</option>
        @foreach ($visions as $id => $label)
            <option value="{{ $id }}" {{ $->vision_id == $id ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
    <input type="text" name="title" value="''" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
    <textarea name="description" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">{{ '' }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Owner') }}</label>
    <select name="owner_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
        <option value="">—</option>
        @foreach ($users as $id => $label)
            <option value="{{ $id }}" {{ $->owner_id == $id ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
    <select name="status" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
        <option value="active" {{ null == "active" ? "selected" : "" }}>{{ __('active') }}</option><option value="paused" {{ null == "paused" ? "selected" : "" }}>{{ __('paused') }}</option><option value="completed" {{ null == "completed" ? "selected" : "" }}>{{ __('completed') }}</option><option value="archived" {{ null == "archived" ? "selected" : "" }}>{{ __('archived') }}</option>
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Review Cadence') }}</label>
    <select name="review_cadence" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
        <option value="monthly" {{ null == "monthly" ? "selected" : "" }}>{{ __('monthly') }}</option><option value="quarterly" {{ null == "quarterly" ? "selected" : "" }}>{{ __('quarterly') }}</option><option value="biannually" {{ null == "biannually" ? "selected" : "" }}>{{ __('biannually') }}</option><option value="annually" {{ null == "annually" ? "selected" : "" }}>{{ __('annually') }}</option>
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Next Review') }}</label>
    <input type="date" name="next_review_at" value="''" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
</div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('visionflow.organizations.missions.index', $organization) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
