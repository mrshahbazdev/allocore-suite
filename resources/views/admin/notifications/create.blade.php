@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.notifications.send_title') }}</h1>
        <p class="text-sm text-slate-500">{{ __('admin.notifications.send_description') }}</p>
    </div>

    <div class="max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.notifications.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Recipients') }}</label>
                <select name="recipient" id="recipient" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all">{{ __('All active users') }}</option>
                    <option value="selected">{{ __('Selected users') }}</option>
                </select>
            </div>

            <div id="user-select" class="hidden">
                <label class="block text-sm font-medium text-slate-700">{{ __('Users') }}</label>
                <select name="user_ids[]" multiple class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($users as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Subject') }}</label>
                <input name="subject" value="{{ old('subject') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Body') }}</label>
                <textarea name="body" rows="4" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('body') }}</textarea>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Type') }}</label>
                    <select name="type" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="info">{{ __('Info') }}</option>
                        <option value="success">{{ __('Success') }}</option>
                        <option value="warning">{{ __('Warning') }}</option>
                        <option value="danger">{{ __('Danger') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Action URL') }}</label>
                    <input name="action_url" value="{{ old('action_url') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Action Text') }}</label>
                <input name="action_text" value="{{ old('action_text') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.notifications.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.notifications.send_button') }}</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('recipient')?.addEventListener('change', function (e) {
            document.getElementById('user-select').classList.toggle('hidden', e.target.value !== 'selected');
        });
    </script>
@endsection
