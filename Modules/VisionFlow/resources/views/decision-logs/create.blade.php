@extends('layouts.shell', ['title' => __('New Decision Log')])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ __('New Decision Log') }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        
@if (session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 mb-6">{{ session('success') }}</div>
@endif

        <form method="POST" action="{{ route('visionflow.organizations.decision-logs.store', $organization) }}" class="space-y-4">
            @csrf
            <div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
    <input type="text" name="title" value="''" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
    <textarea name="description" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">{{ '' }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Decision') }}</label>
    <textarea name="decision" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>{{ '' }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Supporting Value') }}</label>
    <select name="supporting_value_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
        <option value="">—</option>
        @foreach ($values as $id => $label)
            <option value="{{ $id }}" {{ $->supporting_value_id == $id ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Supporting Mission') }}</label>
    <select name="supporting_mission_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
        <option value="">—</option>
        @foreach ($missions as $id => $label)
            <option value="{{ $id }}" {{ $->supporting_mission_id == $id ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('visionflow.organizations.decision-logs.index', $organization) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
