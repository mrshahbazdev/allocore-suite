@extends('layouts.shell', ['title' => __('Edit Value')])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ __('Edit Value') }}</h1>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        
@if (session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 mb-6">{{ session('success') }}</div>
@endif

        <form method="POST" action="{{ route('visionflow.organizations.values.update', [$organization, $item]) }}" class="space-y-4">
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
    <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
    <select name="status" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm" required>
        <option value="draft" {{ $item->status == "draft" ? "selected" : "" }}>{{ __('draft') }}</option><option value="proposed" {{ $item->status == "proposed" ? "selected" : "" }}>{{ __('proposed') }}</option><option value="approved" {{ $item->status == "approved" ? "selected" : "" }}>{{ __('approved') }}</option><option value="archived" {{ $item->status == "archived" ? "selected" : "" }}>{{ __('archived') }}</option>
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Sort Order') }}</label>
    <input type="number" name="sort_order" value="$item->sort_order" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm">
</div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('visionflow.organizations.values.index', $organization) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
