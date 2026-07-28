@extends('layouts.shell', ['title' => __('New Strategic Goal')])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('visionflow.organizations.show', $organization) }}" class="hover:text-indigo-600">{{ $organization->name }}</a>
        <span>/</span>
        <a href="{{ route('visionflow.organizations.strategic-goals.index', $organization) }}" class="hover:text-indigo-600">{{ __('Strategic Goals') }}</a>
        <span>/</span>
        <span class="text-slate-900">{{ __('New Strategic Goal') }}</span>
    </div>

    <h1 class="text-3xl font-bold text-slate-900">{{ __('New Strategic Goal') }}</h1>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('visionflow.organizations.strategic-goals.store', $organization) }}" class="space-y-5">
            @csrf
            <div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Title") }}</label>
    <input type="text" name="title"  class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Description") }}</label>
    <textarea name="description" rows="4" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Category") }}</label>
    <select name="category" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <option value="market" >{{ __("market") }}</option>
        <option value="impact" >{{ __("impact") }}</option>
        <option value="organization" >{{ __("organization") }}</option>
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Time Horizon") }}</label>
    <input type="text" name="time_horizon"  class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Status") }}</label>
    <select name="status" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <option value="active" >{{ __("active") }}</option>
        <option value="archived" >{{ __("archived") }}</option>
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Values") }}</label>
    <select name="values[]" multiple class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach ($values as $id => $label)
            <option value="{{ $id }}" >{{ $label }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-slate-700">{{ __("Principles") }}</label>
    <select name="principles[]" multiple class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach ($principles as $id => $label)
            <option value="{{ $id }}" >{{ $label }}</option>
        @endforeach
    </select>
</div>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('visionflow.organizations.strategic-goals.index', $organization) }}" class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Cancel') }}</a>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Create Strategic Goal') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
