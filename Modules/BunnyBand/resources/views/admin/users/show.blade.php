@extends('layouts.shell')

@section('title', __('User Detail'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ $profile->user->name }}</h1>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p>{{ __('Balance') }}: {{ number_format($profile->balance, 2) }}</p>
            <p>{{ __('Task Earnings') }}: {{ number_format($profile->task_earnings, 2) }}</p>
            <p>{{ __('Referral Earnings') }}: {{ number_format($profile->referral_earnings, 2) }}</p>
            <p>{{ __('Total Referrals') }}: {{ $profile->total_referrals }}</p>
        </div>

        <form method="POST" action="{{ route('bunnyband.admin.users.balance', $profile) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">@csrf
            <h2 class="text-lg font-semibold">{{ __('Adjust Balance') }}</h2>
            <div class="flex gap-2"><input type="number" step="0.01" name="amount" placeholder="Amount" class="rounded-lg border-slate-300"><select name="type" class="rounded-lg border-slate-300"><option value="add">Add</option><option value="subtract">Subtract</option></select></div>
            <div><input type="text" name="reason" placeholder="Reason" class="w-full rounded-lg border-slate-300"></div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Adjust') }}</button>
        </form>

        <form method="POST" action="{{ route('bunnyband.admin.users.block', $profile) }}" class="inline">@csrf @method('PUT')<button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">{{ $profile->is_blocked ? __('Unblock') : __('Block') }}</button></form>
    </div>
@endsection
