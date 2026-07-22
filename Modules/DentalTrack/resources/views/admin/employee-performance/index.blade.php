@extends('layouts.shell')

@section('title', __('Employee Performance'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Employee Performance') }}</h1>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50"><tr><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Technician') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Orders Completed') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Steps Completed') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Avg Min/Step') }}</th><th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ __('Total Hours') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($performance as $row)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium">{{ $row['user']->name }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['orders_completed'] }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['steps_completed'] }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['avg_minutes'] }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['total_hours'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-4 text-sm text-slate-500">{{ __('No performance data.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
