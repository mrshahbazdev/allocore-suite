@extends('layouts.shell')

@section('title', __('Bank Transactions'))
@section('page-title', __('Bank Transactions'))

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Bank Transactions') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Import CSV or MT940 files and review your cashflow forecast.') }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Current Balance') }}</div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($cashflow['current_balance'], 2) }} EUR</div>
            </div>
            @foreach ($cashflow['months'] as $month)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $month['label'] }}</div>
                    <div class="mt-2 text-lg font-bold text-slate-900">{{ number_format($month['ending_balance'], 2) }} EUR</div>
                    <div class="mt-1 text-xs text-slate-500">{{ __('Incoming') }} {{ number_format($month['incoming'], 2) }} / {{ __('Outgoing') }} {{ number_format($month['outgoing'], 2) }}</div>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('financialplatform.bank-transactions.import') }}" enctype="multipart/form-data" class="flex items-end gap-4">
                @csrf
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700">{{ __('Import file') }}</label>
                    <input type="file" name="file" accept=".csv,.mt940,.txt" class="mt-1 block w-full rounded-lg border-slate-300 text-sm" required>
                </div>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Import') }}</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Transactions') }}</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="pb-2 pr-4">{{ __('Date') }}</th>
                            <th class="pb-2 pr-4">{{ __('Description') }}</th>
                            <th class="pb-2 pr-4">{{ __('Category') }}</th>
                            <th class="pb-2 pr-4">{{ __('Type') }}</th>
                            <th class="pb-2 pr-4 text-right">{{ __('Amount') }}</th>
                            <th class="pb-2 pr-4">{{ __('Currency') }}</th>
                            <th class="pb-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td class="py-2 pr-4">{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                                <td class="py-2 pr-4">{{ $transaction->description }}</td>
                                <td class="py-2 pr-4">{{ $transaction->category }}</td>
                                <td class="py-2 pr-4 capitalize">{{ $transaction->type }}</td>
                                <td class="py-2 pr-4 text-right">{{ number_format($transaction->amount, 2) }}</td>
                                <td class="py-2 pr-4">{{ $transaction->currency }}</td>
                                <td class="py-2">
                                    <form method="POST" action="{{ route('financialplatform.bank-transactions.destroy', $transaction) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm text-rose-600">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $transactions->links() }}</div>
        </div>
    </div>
@endsection
