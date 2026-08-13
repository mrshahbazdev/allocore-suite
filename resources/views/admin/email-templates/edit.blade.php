@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.email_templates.edit_title') }}</h1>
        <p class="text-sm text-slate-500">{{ $template->tool }} / {{ $template->key }}</p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.email-templates.update', $template) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Tool') }}</label>
                    <select name="tool" class="w-full rounded-lg border-slate-300 text-sm">
                        @foreach($tools as $key => $label)
                            <option value="{{ $key }}" {{ $template->tool === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Key') }}</label>
                    <input type="text" name="key" value="{{ old('key', $template->key) }}" class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Locale') }}</label>
                    <input type="text" name="locale" value="{{ old('locale', $template->locale) }}" class="w-full rounded-lg border-slate-300 text-sm">
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Subject') }}</label>
                <input type="text" name="subject" value="{{ old('subject', $template->subject) }}" class="w-full rounded-lg border-slate-300 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Body (HTML / Blade)') }}</label>
                <textarea name="body" rows="14" class="w-full rounded-lg border-slate-300 font-mono text-sm">{{ old('body', $template->body) }}</textarea>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Variables (comma separated)') }}</label>
                <input type="text" name="variables" value="{{ is_array($template->variables) ? implode(', ', $template->variables) : $template->variables }}" class="w-full rounded-lg border-slate-300 text-sm">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                <label class="text-sm font-medium text-slate-700">{{ __('Active') }}</label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
                <a href="{{ route('admin.email-templates.index', ['tool' => $template->tool]) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
