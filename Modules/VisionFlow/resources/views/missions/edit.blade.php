@extends('layouts.shell', ['title' => __('Edit Mission')])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('visionflow.organizations.show', $organization) }}" class="hover:text-indigo-600">{{ $organization->name }}</a>
        <span>/</span>
        <a href="{{ route('visionflow.organizations.missions.index', $organization) }}" class="hover:text-indigo-600">{{ __('Missions') }}</a>
        <span>/</span>
        <span class="text-slate-900">{{ __('Edit Mission') }}</span>
    </div>

    <h1 class="text-3xl font-bold text-slate-900">{{ __('Edit Mission') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('visionflow.organizations.missions.update', [$organization, $item]) }}" class="space-y-5">
            @csrf @method('PATCH')
            <div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Vision") }}</label>
    <select name="vision_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <option value="">— {{ __('None') }}</option>
        @foreach ($visions as $id => $label)
            <option value="{{ $id }}" {{ $item->vision_id == $id ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Title") }}</label>
    <input type="text" name="title" value="{{ $item->title }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Description") }}</label>
    <textarea name="description" rows="4" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $item->description }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Owner") }}</label>
    <select name="owner_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">— {{ __('None') }}</option>
        @foreach ($users as $id => $label)
            <option value="{{ $id }}" {{ $item->owner_id == $id ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Status") }}</label>
    <select name="status" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <option value="active" {{ $item->status == 'active' ? 'selected' : '' }}>{{ __("active") }}</option>
        <option value="paused" {{ $item->status == 'paused' ? 'selected' : '' }}>{{ __("paused") }}</option>
        <option value="completed" {{ $item->status == 'completed' ? 'selected' : '' }}>{{ __("completed") }}</option>
        <option value="archived" {{ $item->status == 'archived' ? 'selected' : '' }}>{{ __("archived") }}</option>
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Review Cadence") }}</label>
    <select name="review_cadence" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="monthly" {{ $item->review_cadence == 'monthly' ? 'selected' : '' }}>{{ __("monthly") }}</option>
        <option value="quarterly" {{ $item->review_cadence == 'quarterly' ? 'selected' : '' }}>{{ __("quarterly") }}</option>
        <option value="biannually" {{ $item->review_cadence == 'biannually' ? 'selected' : '' }}>{{ __("biannually") }}</option>
        <option value="annually" {{ $item->review_cadence == 'annually' ? 'selected' : '' }}>{{ __("annually") }}</option>
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Next Review") }}</label>
    <input type="date" name="next_review_at" value="{{ $item->next_review_at }}" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('visionflow.organizations.missions.index', $organization) }}" class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Update Mission') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
