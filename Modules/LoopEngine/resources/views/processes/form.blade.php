@extends('layouts.shell')

@section('title', $process->exists ? __('Edit Process') : __('New Process'))
@section('page-title', $process->exists ? __('Edit Process') : __('New Process'))

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $process->exists ? __('Edit Process') : __('New Process') }}</h1>
        <form method="POST" action="{{ $process->exists ? route('loopengine.processes.update', $process) : route('loopengine.processes.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($process->exists)
                @method('PUT')
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Name (EN)') }}</label><input type="text" name="name_en" value="{{ old('name_en', $process->name_en) }}" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Name (DE)') }}</label><input type="text" name="name_de" value="{{ old('name_de', $process->name_de) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Description (EN)') }}</label><textarea name="description_en" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description_en', $process->description_en) }}</textarea></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Description (DE)') }}</label><textarea name="description_de" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description_de', $process->description_de) }}</textarea></div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Category') }}</label><input type="text" name="category" value="{{ old('category', $process->category) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Icon') }}</label><input type="text" name="icon" value="{{ old('icon', $process->icon) }}" class="mt-1 w-full rounded-lg border-slate-300" placeholder="e.g. document-check"></div>
            </div>

            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
        </form>
    </div>
@endsection
