@extends('layouts.shell')

@section('title', __('Wallet'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Wallet') }}</h1>

        <div class="grid gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Balance') }}</div><div class="text-2xl font-bold">{{ number_format($profile->balance, 2) }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Task Earnings') }}</div><div class="text-2xl font-bold">{{ number_format($profile->task_earnings, 2) }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Referral Earnings') }}</div><div class="text-2xl font-bold">{{ number_format($profile->referral_earnings, 2) }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Min Withdrawal') }}</div><div class="text-2xl font-bold">{{ number_format($minimumWithdrawal, 2) }}</div></div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Withdraw') }}</h2>
                <form method="POST" action="{{ route('bunnyband.wallet.withdraw') }}" class="mt-4 space-y-3">@csrf
                    <div><input type="number" step="0.01" name="amount" placeholder="{{ __('Amount') }}" class="w-full rounded-lg border-slate-300"></div>
                    <div>
                        <select name="bunnyband_withdrawal_method_id" class="w-full rounded-lg border-slate-300">
                            <option value="">{{ __('Method') }}</option>
                            @foreach ($withdrawalMethods as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div><textarea name="account_details" placeholder="{{ __('Account details') }}" rows="2" class="w-full rounded-lg border-slate-300"></textarea></div>
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Request Withdrawal') }}</button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Deposit') }}</h2>
                <form method="POST" action="{{ route('bunnyband.wallet.deposit') }}" enctype="multipart/form-data" class="mt-4 space-y-3">@csrf
                    <div><input type="number" step="0.01" name="amount" placeholder="{{ __('Amount') }}" class="w-full rounded-lg border-slate-300"></div>
                    <div>
                        <select name="bunnyband_deposit_method_id" class="w-full rounded-lg border-slate-300">
                            <option value="">{{ __('Method') }}</option>
                            @foreach ($depositMethods as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div><input type="file" name="screenshot" class="block w-full text-sm"></div>
                    <div><textarea name="account_details" placeholder="{{ __('Account details / reference') }}" rows="2" class="w-full rounded-lg border-slate-300"></textarea></div>
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Request Deposit') }}</button>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Transactions') }}</h2>
            <table class="mt-3 min-w-full text-sm">
                <thead class="text-left text-xs uppercase text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Date') }}</th><th class="pb-2 pr-4">{{ __('Type') }}</th><th class="pb-2 pr-4">{{ __('Amount') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($transactions as $tx)
                        <tr>
                            <td class="py-2 pr-4">{{ $tx->created_at->format('Y-m-d') }}</td>
                            <td class="py-2 pr-4">{{ $tx->type }}</td>
                            <td class="py-2 pr-4">{{ number_format($tx->amount, 2) }}</td>
                            <td class="py-2 pr-4">{{ $tx->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $transactions->links() }}</div>
        </div>
    </div>
@endsection
