@extends('layouts.shell')

@section('title', __('Holidays'))
@section('page-title', __('Holidays'))

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Holidays') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Import German public holidays and weekends by Bundesland.') }}</p>

            <form method="POST" action="{{ route('timebutler.holidays.import') }}" class="mt-4 flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Year') }}</label>
                    <input type="number" name="year" value="{{ $year }}" class="mt-1 rounded-lg border-slate-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Federal State') }}</label>
                    <select name="state" class="mt-1 rounded-lg border-slate-300" required>
                        @foreach ($states as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="include_weekends" value="1" class="rounded border-slate-300"> {{ __('Include weekends') }}</label>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Import') }}</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="GET" class="mb-4 flex gap-3">
                <input type="number" name="year" value="{{ $year }}" class="rounded-lg border-slate-300">
                <button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Filter') }}</button>
            </form>

            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="pb-2 pr-4">{{ __('Date') }}</th><th class="pb-2 pr-4">{{ __('Name') }}</th><th class="pb-2 pr-4">{{ __('Type') }}</th><th class="pb-2"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($holidays as $holiday)
                        <tr>
                            <td class="py-2 pr-4">{{ $holiday->date->format('Y-m-d') }}</td>
                            <td class="py-2 pr-4">{{ $holiday->name }}</td>
                            <td class="py-2 pr-4 capitalize">{{ $holiday->type }}</td>
                            <td class="py-2">
                                <form method="POST" action="{{ route('timebutler.holidays.destroy', $holiday) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-600">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $holidays->links() }}</div>
        </div>
    </div>
@endsection
