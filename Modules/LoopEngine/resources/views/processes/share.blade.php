@extends('layouts.shell')

@section('title', __('Share Process'))
@section('page-title', __('Share Process'))

@section('content')
    <div class="space-y-6 max-w-2xl">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Share as Template') }}: {{ $process->localizedName() }}</h1>
            <a href="{{ route('loopengine.processes.show', $process) }}" class="text-indigo-600">{{ __('Back') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('loopengine.templates.share', $process) }}" class="space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Name (EN)') }}</label>
                        <input type="text" name="name_en" value="{{ old('name_en', $process->name_en) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Name (DE)') }}</label>
                        <input type="text" name="name_de" value="{{ old('name_de', $process->name_de) }}" class="mt-1 w-full rounded-lg border-slate-300">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Description (EN)') }}</label>
                        <textarea name="description_en" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description_en', $process->description_en) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Description (DE)') }}</label>
                        <textarea name="description_de" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('description_de', $process->description_de) }}</textarea>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Category') }}</label>
                        <input type="text" name="category" value="{{ old('category', $process->category) }}" class="mt-1 w-full rounded-lg border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Tags') }}</label>
                        <input type="text" name="tags" value="{{ old('tags') }}" class="mt-1 w-full rounded-lg border-slate-300" placeholder="sales, onboarding">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_public" value="1" {{ old('is_public', true) ? 'checked' : '' }} class="rounded border-slate-300">
                    <span class="text-sm text-slate-700">{{ __('Public') }}</span>
                </div>

                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Share') }}</button>
            </form>
        </div>
    </div>
@endsection
