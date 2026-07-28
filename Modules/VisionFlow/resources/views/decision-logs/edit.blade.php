@extends('layouts.shell', ['title' => __('Edit Decision Log')])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('visionflow.organizations.show', $organization) }}" class="hover:text-indigo-600">{{ $organization->name }}</a>
        <span>/</span>
        <a href="{{ route('visionflow.organizations.decision-logs.index', $organization) }}" class="hover:text-indigo-600">{{ __('Decision Log') }}</a>
        <span>/</span>
        <span class="text-slate-900">{{ __('Edit Decision Log') }}</span>
    </div>

    <h1 class="text-3xl font-bold text-slate-900">{{ __('Edit Decision Log') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('visionflow.organizations.decision-logs.update', [$organization, $item]) }}" class="space-y-5">
            @csrf @method('PATCH')
            <div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Title") }}</label>
    <input type="text" name="title" value="{{ $item->title }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Description") }}</label>
    <textarea name="description" rows="4" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $item->description }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Decision") }}</label>
    <textarea name="decision" rows="4" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ $item->decision }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Supporting Value") }}</label>
    <select name="supporting_value_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">— {{ __('None') }}</option>
        @foreach ($values as $id => $label)
            <option value="{{ $id }}" {{ $item->supporting_value_id == $id ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Supporting Mission") }}</label>
    <select name="supporting_mission_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">— {{ __('None') }}</option>
        @foreach ($missions as $id => $label)
            <option value="{{ $id }}" {{ $item->supporting_mission_id == $id ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('visionflow.organizations.decision-logs.index', $organization) }}" class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Update Decision Log') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
