@extends('layouts.shell')

@section('title', __('Exchange Rates'))
@section('page-title', __('Exchange Rates'))

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h1 class="text-2xl font-bold text-slate-900">{{ __('Exchange Rates') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Maintain currency conversion rates for multi-currency reporting.') }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="pb-2 pr-4">{{ __('From') }}</th>
                            <th class="pb-2 pr-4">{{ __('To') }}</th>
                            <th class="pb-2 pr-4 text-right">{{ __('Rate') }}</th>
                            <th class="pb-2 pr-4">{{ __('Date') }}</th>
                            <th class="pb-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($rates as $rate)
                            <tr>
                                <td class="py-2 pr-4 font-medium text-slate-900">{{ $rate->from_currency }}</td>
                                <td class="py-2 pr-4">{{ $rate->to_currency }}</td>
                                <td class="py-2 pr-4 text-right">{{ number_format($rate->rate, 8) }}</td>
                                <td class="py-2 pr-4">{{ $rate->date->format('Y-m-d') }}</td>
                                <td class="py-2">
                                    <form method="POST" action="{{ route('financialplatform.exchange-rates.destroy', $rate) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm text-rose-600">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">{{ $rates->links() }}</div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Add Rate') }}</h2>
            <form method="POST" action="{{ route('financialplatform.exchange-rates.store') }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('From') }}</label>
                        <input type="text" name="from_currency" maxlength="3" class="mt-1 w-full rounded-lg border-slate-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('To') }}</label>
                        <input type="text" name="to_currency" maxlength="3" class="mt-1 w-full rounded-lg border-slate-300" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Rate') }}</label>
                    <input type="number" step="0.00000001" name="rate" class="mt-1 w-full rounded-lg border-slate-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Date') }}</label>
                    <input type="date" name="date" value="{{ now()->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border-slate-300" required>
                </div>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </form>
        </div>
    </div>
@endsection
