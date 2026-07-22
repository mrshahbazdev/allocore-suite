@extends('layouts.shell')

@section('title', __('BunnyBand Admin'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('BunnyBand Admin') }}</h1>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($stats as $key => $value)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __(str_replace('_', ' ', $key)) }}</div><div class="text-2xl font-bold">{{ is_numeric($value) ? number_format($value, 2) : $value }}</div></div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Recent Transactions') }}</h2>
            <table class="mt-3 min-w-full text-sm">
                <thead class="text-left text-xs uppercase text-slate-500"><tr><th class="pb-2 pr-4">{{ __('User') }}</th><th class="pb-2 pr-4">{{ __('Type') }}</th><th class="pb-2 pr-4">{{ __('Amount') }}</th><th class="pb-2 pr-4">{{ __('Status') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($recentTransactions as $tx)
                        <tr>
                            <td class="py-2 pr-4">{{ $tx->profile?->user?->name }}</td>
                            <td class="py-2 pr-4">{{ $tx->type }}</td>
                            <td class="py-2 pr-4">{{ number_format($tx->amount, 2) }}</td>
                            <td class="py-2 pr-4">{{ $tx->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
