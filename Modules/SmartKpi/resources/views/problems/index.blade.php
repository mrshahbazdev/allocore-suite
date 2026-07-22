@extends('layouts.shell')

@section('title', __('Problems'))

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Problems') }}</h1>
            <a href="{{ route('smartkpi.problems.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Problem') }}</a>
        </div>

        <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                <select name="status" class="mt-1 rounded-lg border-slate-300">
                    <option value="">{{ __('All') }}</option>
                    @foreach (['open', 'in_progress', 'resolved', 'closed'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ __($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('Severity') }}</label>
                <select name="severity" class="mt-1 rounded-lg border-slate-300">
                    <option value="">{{ __('All') }}</option>
                    @foreach (['warning', 'critical', 'anomaly'] as $s)
                        <option value="{{ $s }}" {{ request('severity') === $s ? 'selected' : '' }}>{{ __($s) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Filter') }}</button>
        </form>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Title') }}</th><th class="pb-2 pr-4">{{ __('KPI') }}</th><th class="pb-2 pr-4">{{ __('Severity') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($problems as $problem)
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $problem->title }}</td>
                            <td class="py-2 pr-4">{{ $problem->kpiDefinition?->localizedName() }}</td>
                            <td class="py-2 pr-4">{{ $problem->severity }}</td>
                            <td class="py-2 pr-4">{{ $problem->status }}</td>
                            <td class="py-2"><a href="{{ route('smartkpi.problems.show', $problem) }}" class="text-indigo-600">{{ __('Open') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $problems->links() }}</div>
        </div>
    </div>
@endsection
