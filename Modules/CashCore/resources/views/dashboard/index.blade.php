@extends('layouts.shell')

@section('title', __('CashCore'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('CashCore') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Profit First financial intelligence: see where your money goes and unlock hidden capital.') }}</p>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <input type="month" name="period" value="{{ $period }}" class="rounded-lg border-slate-300 text-sm">
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Update') }}</button>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Income') }}</div><div class="text-2xl font-bold">{{ number_format($income, 2) }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Expenses') }}</div><div class="text-2xl font-bold">{{ number_format($expenses, 2) }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Net Profit') }}</div><div class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($netProfit, 2) }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Profit Margin') }}</div><div class="text-2xl font-bold">{{ $profitMargin }}%</div></div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Cost Ratio') }}</div><div class="text-xl font-bold">{{ $costRatio }}%</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Overhead Ratio') }}</div><div class="text-xl font-bold">{{ $overheadRatio }}%</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Active Leaks') }}</div><div class="text-xl font-bold {{ $activeLeaks > 0 ? 'text-rose-600' : '' }}">{{ $activeLeaks }}</div></div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Transactions') }}</h2>
            <table class="mt-3 min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Date') }}</th><th class="pb-2 pr-4">{{ __('Description') }}</th><th class="pb-2 pr-4">{{ __('Type') }}</th><th class="pb-2 pr-4">{{ __('Amount') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($recentTransactions as $tx)
                        <tr>
                            <td class="py-2 pr-4">{{ $tx->transaction_date->format('Y-m-d') }}</td>
                            <td class="py-2 pr-4">{{ $tx->description }}</td>
                            <td class="py-2 pr-4">{{ __($tx->type) }}</td>
                            <td class="py-2 pr-4">{{ number_format($tx->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
