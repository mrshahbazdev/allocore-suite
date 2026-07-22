@extends('layouts.shell')

@section('title', __('Withdrawals'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Withdrawals') }}</h1>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase text-slate-500"><tr><th class="pb-2 pr-4">{{ __('User') }}</th><th class="pb-2 pr-4">{{ __('Amount') }}</th><th class="pb-2 pr-4">{{ __('Method') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($withdrawals as $tx)
                        <tr>
                            <td class="py-2 pr-4">{{ $tx->profile?->user?->name }}</td>
                            <td class="py-2 pr-4">{{ number_format($tx->amount, 2) }}</td>
                            <td class="py-2 pr-4">{{ $tx->payment_method }}</td>
                            <td class="py-2 pr-4">{{ $tx->status }}</td>
                            <td class="py-2">
                                @if ($tx->status === 'pending')
                                    <form method="POST" action="{{ route('bunnyband.admin.withdrawals.approve', $tx) }}" class="inline">@csrf<button class="text-emerald-600">{{ __('Approve') }}</button></form>
                                    <form method="POST" action="{{ route('bunnyband.admin.withdrawals.reject', $tx) }}" class="inline">@csrf<button class="text-rose-600">{{ __('Reject') }}</button></form>
                                @elseif ($tx->status === 'approved')
                                    <form method="POST" action="{{ route('bunnyband.admin.withdrawals.complete', $tx) }}" class="inline">@csrf<input type="text" name="payment_proof" placeholder="Proof" class="rounded border-slate-300 text-xs"><button class="text-indigo-600">{{ __('Complete') }}</button></form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $withdrawals->links() }}</div>
        </div>
    </div>
@endsection
