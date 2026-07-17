@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.api_tokens.create_title') }}</h1>
        <p class="text-sm text-slate-500">{{ __('admin.api_tokens.create_description') }}</p>
    </div>

    <div class="max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.api-tokens.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('User') }}</label>
                <select name="user_id" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($users as $id => $name)
                        <option value="{{ $id }}" @selected(old('user_id') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                <input name="name" value="{{ old('name') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('admin.api_tokens.abilities') }}</label>
                <input name="abilities" value="{{ old('abilities') }}" placeholder="read, write" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-slate-500">{{ __('admin.api_tokens.abilities_help') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('admin.api_tokens.expires_at') }}</label>
                <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.api-tokens.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.api_tokens.create_button') }}</button>
            </div>
        </form>
    </div>
@endsection
