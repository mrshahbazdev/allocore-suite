@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.maintenance.title') }}</h1>
        <p class="text-sm text-slate-500">{{ __('admin.maintenance.description') }}</p>
    </div>

    <div class="max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.maintenance.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-2">
                <input id="maintenance_mode" name="maintenance_mode" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked($mode)>
                <label for="maintenance_mode" class="text-sm font-medium text-slate-700">{{ __('admin.maintenance.enable') }}</label>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('admin.maintenance.message') }}</label>
                <textarea name="maintenance_message" rows="3" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('maintenance_message', $message) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.maintenance.save_button') }}</button>
            </div>
        </form>
    </div>
@endsection
