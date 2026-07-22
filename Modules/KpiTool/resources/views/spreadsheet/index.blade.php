@extends('layouts.shell')

@section('title', __('KPI Spreadsheet'))
@section('page-title', __('KPI Spreadsheet'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Monthly Spreadsheet') }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('kpitool.spreadsheet.export', ['year' => $year, 'month' => $month]) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Export CSV') }}</a>
            </div>
        </div>

        <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex flex-wrap items-end gap-3">
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Year') }}</label><input type="number" name="year" value="{{ $year }}" class="mt-1 rounded-lg border-slate-300"></div>
            <div><label class="block text-sm font-medium text-slate-700">{{ __('Month') }}</label><input type="number" name="month" min="1" max="12" value="{{ $month }}" class="mt-1 rounded-lg border-slate-300"></div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Show') }}</button>
        </form>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="pb-2 pr-4">{{ __('KPI') }}</th>
                        @foreach ($days as $day)
                            <th class="pb-2 pr-2">{{ \Carbon\Carbon::parse($day)->format('d') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($definitions as $definition)
                        <tr>
                            <td class="py-2 pr-4 font-medium whitespace-nowrap">{{ $definition->name }}</td>
                            @foreach ($days as $day)
                                @php($value = $values->get($definition->id.'-'.$day))
                                <td class="py-2 pr-2 text-center">{{ $value?->value ?? '-' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Import CSV') }}</h2>
            <p class="text-sm text-slate-500">{{ __('Columns: Date, KPI, Value, Notes') }}</p>
            <form method="POST" action="{{ route('kpitool.spreadsheet.import') }}" enctype="multipart/form-data" class="mt-4 flex items-end gap-3">
                @csrf
                <div><label class="block text-sm font-medium text-slate-700">{{ __('File') }}</label><input type="file" name="file" class="mt-1 rounded-lg border-slate-300" required></div>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Import') }}</button>
            </form>
        </div>
    </div>
@endsection
