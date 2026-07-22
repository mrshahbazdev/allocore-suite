@extends('layouts.shell')

@section('title', __('Referrals'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Referrals') }}</h1>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Your Referral Link') }}</h2>
            <input type="text" value="{{ $link }}" readonly class="mt-2 w-full rounded-lg border-slate-300 bg-slate-50">
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Claim Referral Code') }}</h2>
            <form method="POST" action="{{ route('bunnyband.referrals.claim') }}" class="mt-3 flex gap-2">@csrf<input type="text" name="referral_code" placeholder="BNY-XXXX" class="rounded-lg border-slate-300"><button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Claim') }}</button></form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('My Referrals') }}</h2>
            <table class="mt-3 min-w-full text-sm">
                <thead class="text-left text-xs uppercase text-slate-500"><tr><th class="pb-2 pr-4">{{ __('User') }}</th><th class="pb-2 pr-4">{{ __('Reward') }}</th><th class="pb-2 pr-4">{{ __('Date') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($referrals as $ref)
                        <tr>
                            <td class="py-2 pr-4">{{ $ref->referred?->user?->name }}</td>
                            <td class="py-2 pr-4">{{ number_format($ref->reward_amount, 2) }}</td>
                            <td class="py-2 pr-4">{{ $ref->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $referrals->links() }}</div>
        </div>
    </div>
@endsection
