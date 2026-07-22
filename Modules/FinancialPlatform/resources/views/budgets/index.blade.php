@extends('layouts.shell')

@section('title', __('Budgets'))
@section('page-title', __('Budgets'))

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h1 class="text-2xl font-bold text-slate-900">{{ __('Budgets') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Plan expenses by category and compare with actuals.') }}</p>
            </div>

            <form method="GET" class="flex items-end gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Year') }}</label>
                    <input type="number" name="year" value="{{ $year }}" class="mt-1 rounded-lg border-slate-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Month') }}</label>
                    <input type="number" name="month" value="{{ $month }}" min="1" max="12" class="mt-1 rounded-lg border-slate-300">
                </div>
                <button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Filter') }}</button>
            </form>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Budget vs Actual') }}</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="pb-2 pr-4">{{ __('Category') }}</th>
                                <th class="pb-2 pr-4 text-right">{{ __('Budget') }}</th>
                                <th class="pb-2 pr-4 text-right">{{ __('Actual') }}</th>
                                <th class="pb-2 pr-4 text-right">{{ __('Variance') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($budgets as $budget)
                                @php($actual = (float) ($actual[$budget->category] ?? 0))
                                @php($variance = $budget->amount - $actual)
                                <tr>
                                    <td class="py-2 pr-4 font-medium text-slate-900">{{ $budget->category }}</td>
                                    <td class="py-2 pr-4 text-right">{{ number_format($budget->amount, 2) }}</td>
                                    <td class="py-2 pr-4 text-right">{{ number_format($actual, 2) }}</td>
                                    <td class="py-2 pr-4 text-right {{ $variance >= 0 ? 'text-green-600' : 'text-rose-600' }}">{{ number_format($variance, 2) }}</td>
                                </tr>
                            @endforeach
                            @if ($budgets->isEmpty())
                                <tr><td colspan="4" class="py-4 text-slate-500">{{ __('No budgets for this period.') }}</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Add Budget') }}</h2>
            <form method="POST" action="{{ route('financialplatform.budgets.store') }}" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Category') }}</label>
                    <input type="text" name="category" class="mt-1 w-full rounded-lg border-slate-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Amount') }}</label>
                    <input type="number" step="0.01" name="amount" class="mt-1 w-full rounded-lg border-slate-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Currency') }}</label>
                    <input type="text" name="currency" value="EUR" maxlength="3" class="mt-1 w-full rounded-lg border-slate-300">
                </div>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save') }}</button>
            </form>
        </div>
    </div>
@endsection
