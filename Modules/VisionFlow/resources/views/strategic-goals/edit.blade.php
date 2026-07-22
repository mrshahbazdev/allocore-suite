@extends('layouts.shell', ['title' => __('Edit Strategic Goal')])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ __('Edit Strategic Goal') }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        
@if (session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 mb-6">{{ session('success') }}</div>
@endif

        <form method="POST" action="{{ route('visionflow.organizations.strategic-goals.update', [$organization, $item]) }}" class="space-y-4">
            @csrf @method('PATCH')
            <div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
    <input type="text" name="title" value="$item->title" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
    <textarea name="description" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">{{ $item->description }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Category') }}</label>
    <select name="category" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
        <option value="market" {{ $item->category == "market" ? "selected" : "" }}>{{ __('market') }}</option><option value="impact" {{ $item->category == "impact" ? "selected" : "" }}>{{ __('impact') }}</option><option value="organization" {{ $item->category == "organization" ? "selected" : "" }}>{{ __('organization') }}</option>
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Time Horizon') }}</label>
    <input type="text" name="time_horizon" value="$item->time_horizon" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
    <select name="status" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
        <option value="active" {{ $item->status == "active" ? "selected" : "" }}>{{ __('active') }}</option><option value="archived" {{ $item->status == "archived" ? "selected" : "" }}>{{ __('archived') }}</option>
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Values') }}</label>
    <select name="values[]" multiple class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
        @foreach ($values as $id => $label)
            <option value="{{ $id }}" {{ in_array($id, $item->values->pluck('id')->toArray() ?? []) ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Principles') }}</label>
    <select name="principles[]" multiple class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
        @foreach ($principles as $id => $label)
            <option value="{{ $id }}" {{ in_array($id, $item->principles->pluck('id')->toArray() ?? []) ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('visionflow.organizations.strategic-goals.index', $organization) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
