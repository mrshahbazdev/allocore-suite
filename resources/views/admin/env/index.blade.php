@extends('layouts.shell')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('Environment Variables') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Edit .env values directly from the admin panel. Config cache is cleared automatically on save.') }}</p>
            </div>
            @if ($writable)
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">{{ __('.env is writable') }}</span>
            @else
                <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-700">{{ __('.env is not writable') }}</span>
            @endif
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        @if (! $writable)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ __('Make .env writable (chmod 664) to edit values from here.') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.env.update') }}">
            @csrf
            @method('PUT')

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="max-h-[60vh] overflow-y-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="sticky top-0 z-10 bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Key') }}</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Value') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($entries as $index => $entry)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 align-top">
                                        <input type="hidden" name="env[{{ $index }}][key]" value="{{ $entry['key'] }}">
                                        <code class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-700">{{ $entry['key'] }}</code>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <input type="text" name="env[{{ $index }}][value]" value="{{ $entry['value'] }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none" {{ $writable ? '' : 'disabled' }}>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 bg-slate-50 px-4 py-4">
                    <h3 class="mb-3 text-sm font-semibold text-slate-700">{{ __('Add new variable') }}</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <input type="text" name="new_key" placeholder="NEW_KEY" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none" {{ $writable ? '' : 'disabled' }}>
                        <input type="text" name="new_value" placeholder="value" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none" {{ $writable ? '' : 'disabled' }}>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('admin.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50" {{ $writable ? '' : 'disabled' }}>{{ __('Save Changes') }}</button>
            </div>
        </form>
    </div>
@endsection
