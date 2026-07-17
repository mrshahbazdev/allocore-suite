@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.audit_pillars.create_title') }}</h1>
        <p class="text-sm text-slate-500">{{ $template->name }}</p>
    </div>

    <div class="max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.audits.pillars.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="template_id" value="{{ $template->id }}">

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                <input name="name" value="{{ old('name') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                <textarea name="description" rows="3" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('admin.audit_pillars.icon') }}</label>
                    <input name="icon" value="{{ old('icon') }}" placeholder="e.g. chart-bar" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('admin.audit_pillars.target') }}</label>
                    <input name="target_score" type="number" step="0.1" min="0" max="10" value="{{ old('target_score', 5) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('admin.audit_pillars.position') }}</label>
                <input name="position" type="number" min="0" value="{{ old('position', 0) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.audits.templates.edit', $template) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.audit_pillars.create_button') }}</button>
            </div>
        </form>
    </div>
@endsection
