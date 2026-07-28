@extends('layouts.shell', ['title' => __('Edit Principle')])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('visionflow.organizations.show', $organization) }}" class="hover:text-indigo-600">{{ $organization->name }}</a>
        <span>/</span>
        <a href="{{ route('visionflow.organizations.principles.index', $organization) }}" class="hover:text-indigo-600">{{ __('Principles') }}</a>
        <span>/</span>
        <span class="text-slate-900">{{ __('Edit Principle') }}</span>
    </div>

    <h1 class="text-3xl font-bold text-slate-900">{{ __('Edit Principle') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('visionflow.organizations.principles.update', [$organization, $item]) }}" class="space-y-5">
            @csrf @method('PATCH')
            <div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Value") }}</label>
    <select name="value_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <option value="">— {{ __('None') }}</option>
        @foreach ($values as $id => $label)
            <option value="{{ $id }}" {{ $item->value_id == $id ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Statement") }}</label>
    <textarea name="statement" rows="4" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ $item->statement }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Status") }}</label>
    <select name="status" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <option value="draft" {{ $item->status == 'draft' ? 'selected' : '' }}>{{ __("draft") }}</option>
        <option value="proposed" {{ $item->status == 'proposed' ? 'selected' : '' }}>{{ __("proposed") }}</option>
        <option value="approved" {{ $item->status == 'approved' ? 'selected' : '' }}>{{ __("approved") }}</option>
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Alignment Score") }}</label>
    <input type="number" name="alignment_score" value="{{ $item->alignment_score }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('visionflow.organizations.principles.index', $organization) }}" class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Update Principle') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
