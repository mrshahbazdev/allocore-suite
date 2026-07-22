@extends('layouts.shell')

@section('title', __('Scenarios'))

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Scenario Simulator') }}</h1>
            <a href="{{ route('cashcore.scenarios.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Scenario') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Name') }}</th><th class="pb-2 pr-4">{{ __('Current Profit') }}</th><th class="pb-2 pr-4">{{ __('Projected Profit') }}</th><th class="pb-2 pr-4">{{ __('Delta') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($scenarios as $scenario)
                        <tr>
                            <td class="py-2 pr-4 font-medium">{{ $scenario->name }}</td>
                            <td class="py-2 pr-4">{{ number_format($scenario->currentProfit(), 2) }}</td>
                            <td class="py-2 pr-4">{{ number_format($scenario->calculateProjectedProfit(), 2) }}</td>
                            <td class="py-2 pr-4 {{ $scenario->profitDelta() >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($scenario->profitDelta(), 2) }}</td>
                            <td class="py-2">
                                <form method="POST" action="{{ route('cashcore.scenarios.destroy', $scenario) }}" class="inline">@csrf @method('DELETE')<button class="text-rose-600">{{ __('Delete') }}</button></form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
