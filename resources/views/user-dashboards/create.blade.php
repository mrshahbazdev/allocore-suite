@extends('layouts.shell')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Create dashboard') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('Add a title and choose whether this is your default dashboard. You can add and arrange widgets after saving.') }}</p>

        <form method="POST" action="{{ route('dashboards.store') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }} id="is_default" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <label for="is_default" class="text-sm text-slate-700">{{ __('Make default') }}</label>
            </div>

            <input type="hidden" name="widgets" value="[]">

            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('dashboards.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">{{ __('Create') }}</button>
            </div>
        </form>
    </div>
@endsection
