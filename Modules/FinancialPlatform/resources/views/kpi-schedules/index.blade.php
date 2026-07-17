@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('kpi_schedules.title') }}</h1>
        <p class="text-sm text-slate-500">{{ __('kpi_schedules.description') }}</p>
    </div>

    <form method="POST" action="{{ route('financial.kpi-schedules.store') }}" class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('kpi_schedules.frequency') }}</label>
            <select name="frequency" class="mt-2 w-full rounded-lg border-slate-300 text-sm">
                <option value="daily">{{ __('kpi_schedules.daily') }}</option>
                <option value="weekly">{{ __('kpi_schedules.weekly') }}</option>
                <option value="monthly" selected>{{ __('kpi_schedules.monthly') }}</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">{{ __('kpi_schedules.recipients') }}</label>
            <input type="text" name="recipients" required class="mt-2 w-full rounded-lg border-slate-300 text-sm" placeholder="{{ __('kpi_schedules.recipients_placeholder') }}">
            <p class="mt-1 text-xs text-slate-500">{{ __('kpi_schedules.recipients_help') }}</p>
        </div>
        <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('kpi_schedules.create') }}</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('kpi_schedules.frequency') }}</th>
                    <th class="px-4 py-3">{{ __('kpi_schedules.recipients') }}</th>
                    <th class="px-4 py-3">{{ __('kpi_schedules.next_run') }}</th>
                    <th class="px-4 py-3">{{ __('kpi_schedules.last_run') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($schedules as $schedule)
                    <tr>
                        <td class="px-4 py-3 text-slate-900">{{ __("kpi_schedules.{$schedule->frequency}") }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ implode(', ', $schedule->recipients ?? []) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $schedule->next_run_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $schedule->last_run_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <form method="POST" action="{{ route('financial.kpi-schedules.run', $schedule) }}">
                                    @csrf
                                    <button class="text-sm font-medium text-indigo-600 hover:underline">{{ __('kpi_schedules.run_now') }}</button>
                                </form>
                                <form method="POST" action="{{ route('financial.kpi-schedules.destroy', $schedule) }}" onsubmit="return confirm('{{ __('kpi_schedules.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-medium text-rose-600 hover:underline">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">{{ __('kpi_schedules.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $schedules->links() }}</div>
@endsection
