@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Map columns') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Match spreadsheet columns to :module fields.', ['module' => $module->name]) }}</p>
    </div>

    <div class="mb-6 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    @foreach ($headers as $header)
                        <th class="px-4 py-3">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($preview as $row)
                    <tr>
                        @foreach ($headers as $header)
                            <td class="px-4 py-2 text-slate-700">{{ $row[$header] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <form method="POST" action="{{ route('imports.store') }}" class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($columns as $column)
                <div>
                    <label class="block text-xs font-medium text-slate-500">{{ $column }}</label>
                    <select name="mapping[{{ $column }}]" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                        <option value="">{{ __('— Ignore —') }}</option>
                        @foreach ($headers as $header)
                            <option value="{{ $header }}" {{ $column === $header || strtolower($column) === strtolower($header) ? 'selected' : '' }}>{{ $header }}</option>
                        @endforeach
                    </select>
                    @error('mapping.'.$column)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('imports.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Import') }}</button>
        </div>
    </form>
@endsection
