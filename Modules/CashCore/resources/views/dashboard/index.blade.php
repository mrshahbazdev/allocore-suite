@extends('layouts.shell')

@section('title', __('CashCore'))

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">{{ __('CashCore') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Profit First financial intelligence: see where your money goes and unlock hidden capital.') }}</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <input type="month" name="period" value="{{ $period }}" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Update') }}</button>
        </form>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs uppercase text-slate-500">{{ __('Income') }}</div>
                <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.03-.659-1.171-.879-1.172-2.303 0-3.182 1.171-.879 3.07-.879 4.242 0L12 6"/></svg>
            </div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($income, 2) }}</div>
            @if ($incomeChange != 0)
                <div class="mt-1 text-xs {{ $incomeChange >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $incomeChange >= 0 ? '+' : '' }}{{ $incomeChange }}% {{ __('vs last month') }}</div>
            @endif
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs uppercase text-slate-500">{{ __('Expenses') }}</div>
                <svg class="h-5 w-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
            </div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($expenses, 2) }}</div>
            @if ($expenseChange != 0)
                <div class="mt-1 text-xs {{ $expenseChange >= 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $expenseChange >= 0 ? '+' : '' }}{{ $expenseChange }}% {{ __('vs last month') }}</div>
            @endif
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs uppercase text-slate-500">{{ __('Net Profit') }}</div>
                <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75"/></svg>
            </div>
            <div class="mt-1 text-2xl font-bold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($netProfit, 2) }}</div>
            <div class="mt-1 text-xs text-slate-500">{{ $profitMargin }}% {{ __('margin') }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs uppercase text-slate-500">{{ __('Alerts') }}</div>
                <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0V12.75a1.5 1.5 0 00-1.5-1.5H6a1.5 1.5 0 00-1.5 1.5v.75m0 0h15.75M4.5 19.5h15"/></svg>
            </div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ $unreadAlerts }}</div>
            <div class="mt-1 text-xs text-slate-500">{{ $activeLeaks }} {{ __('active leaks') }}</div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('6-Month Trend') }}</h2>
            <div class="mt-4 flex items-end gap-2 h-40">
                @foreach ($monthlyTrend as $trend)
                    @php($max = max(1, collect($monthlyTrend)->max('income')))
                    <div class="flex flex-1 flex-col items-center gap-1">
                        <div class="flex w-full flex-1 items-end gap-0.5">
                            <div class="w-1/2 rounded-t bg-emerald-200" style="height: {{ ($trend['income'] / $max) * 100 }}%"></div>
                            <div class="w-1/2 rounded-t bg-rose-300" style="height: {{ ($trend['expenses'] / $max) * 100 }}%"></div>
                        </div>
                        <div class="text-[10px] text-slate-500">{{ $trend['month_short'] }}</div>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex items-center gap-4 text-xs text-slate-500">
                <span class="flex items-center gap-1"><span class="h-3 w-3 rounded bg-emerald-200"></span>{{ __('Income') }}</span>
                <span class="flex items-center gap-1"><span class="h-3 w-3 rounded bg-rose-300"></span>{{ __('Expenses') }}</span>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Top Expense Categories') }}</h2>
            @if ($categoryBreakdown->isEmpty())
                <p class="mt-3 text-sm text-slate-500">{{ __('No expense data for this period.') }}</p>
            @else
                <ul class="mt-3 space-y-3">
                    @foreach ($categoryBreakdown as $category)
                        <li class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs" style="background-color: {{ $category->color }}20; color: {{ $category->color }}">{{ $category->icon }}</span>
                                <span class="text-slate-700">{{ $category->name }}</span>
                            </div>
                            <span class="font-medium text-slate-900">{{ number_format($category->total, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Transactions') }}</h2>
                <a href="{{ route('cashcore.transactions.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">{{ __('View all') }}</a>
            </div>
            <table class="mt-3 min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Date') }}</th><th class="pb-2 pr-4">{{ __('Description') }}</th><th class="pb-2 pr-4">{{ __('Type') }}</th><th class="pb-2 pr-4">{{ __('Amount') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($recentTransactions as $tx)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2 pr-4 text-slate-600">{{ $tx->transaction_date->format('Y-m-d') }}</td>
                            <td class="py-2 pr-4 font-medium text-slate-900">{{ $tx->description }}</td>
                            <td class="py-2 pr-4"><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $tx->type === 'income' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ __($tx->type) }}</span></td>
                            <td class="py-2 pr-4 font-medium {{ $tx->type === 'income' ? 'text-emerald-600' : 'text-slate-900' }}">{{ number_format($tx->amount, 2) }}</td>
                        </tr>
                    @endforeach
                    @if ($recentTransactions->isEmpty())
                        <tr><td colspan="4" class="py-4 text-sm text-slate-500">{{ __('No transactions yet.') }}</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Health') }}</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <div class="flex justify-between text-sm text-slate-600"><span>{{ __('Cost Ratio') }}</span><span class="font-medium">{{ $costRatio }}%</span></div>
                        <div class="mt-1 h-2 w-full rounded-full bg-slate-100"><div class="h-2 rounded-full bg-indigo-500" style="width: {{ min(100, $costRatio) }}%"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm text-slate-600"><span>{{ __('Overhead Ratio') }}</span><span class="font-medium">{{ $overheadRatio }}%</span></div>
                        <div class="mt-1 h-2 w-full rounded-full bg-slate-100"><div class="h-2 rounded-full bg-amber-400" style="width: {{ min(100, $overheadRatio) }}%"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm text-slate-600"><span>{{ __('Leak Score') }}</span><span class="font-medium">{{ $leakScore }}/100</span></div>
                        <div class="mt-1 h-2 w-full rounded-full bg-slate-100"><div class="h-2 rounded-full {{ $leakScore > 50 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $leakScore }}%"></div></div>
                    </div>
                </div>
            </div>

            <a href="{{ route('cashcore.leaks.index') }}" class="block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-indigo-300">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Active Leaks') }}</h2>
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </div>
                <div class="mt-2 text-3xl font-bold {{ $activeLeaks > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $activeLeaks }}</div>
            </a>
        </div>
    </div>
</div>
@endsection
