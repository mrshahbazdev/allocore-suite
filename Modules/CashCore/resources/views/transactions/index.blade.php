@extends('layouts.shell')

@section('title', __('Transactions'))

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Transactions') }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('cashcore.transactions.import') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Import CSV') }}</a>
                <a href="{{ route('cashcore.transactions.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('New Transaction') }}</a>
            </div>
        </div>

        <form method="GET" class="flex gap-2">
            @foreach (['all' => 'All', 'income' => 'Income', 'expense' => 'Expense'] as $key => $label)
                <a href="{{ route('cashcore.transactions.index', ['filter' => $key]) }}" class="rounded-lg px-3 py-1 text-sm font-medium {{ $filter === $key ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ __($label) }}</a>
            @endforeach
        </form>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Date') }}</th><th class="pb-2 pr-4">{{ __('Description') }}</th><th class="pb-2 pr-4">{{ __('Category') }}</th><th class="pb-2 pr-4">{{ __('Type') }}</th><th class="pb-2 pr-4">{{ __('Amount') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($transactions as $tx)
                        <tr>
                            <td class="py-2 pr-4">{{ $tx->transaction_date->format('Y-m-d') }}</td>
                            <td class="py-2 pr-4 font-medium">{{ $tx->description }}</td>
                            <td class="py-2 pr-4">{{ $tx->category?->name }}</td>
                            <td class="py-2 pr-4">{{ __($tx->type) }}</td>
                            <td class="py-2 pr-4">{{ number_format($tx->amount, 2) }}</td>
                            <td class="py-2"><a href="{{ route('cashcore.transactions.edit', $tx) }}" class="text-indigo-600">{{ __('Edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $transactions->links() }}</div>
        </div>
    </div>
@endsection
