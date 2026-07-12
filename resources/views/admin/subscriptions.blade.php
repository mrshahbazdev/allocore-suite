@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Subscriptions') }}</h1>
    </div>

    <h2 class="text-lg font-semibold text-slate-800 mb-3">{{ __('Pending bank transfers') }}</h2>
    <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden mb-8">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
            <tr>
                <th class="px-4 py-3">{{ __('Buyer') }}</th>
                <th class="px-4 py-3">{{ __('Plan') }}</th>
                <th class="px-4 py-3">{{ __('Reference') }}</th>
                <th class="px-4 py-3">{{ __('Submitted') }}</th>
                <th class="px-4 py-3"></th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($pending as $subscription)
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-900">
                        {{ $subscription->billable?->name ?? '—' }}
                        <span class="text-xs text-slate-400">({{ class_basename($subscription->billable_type) }})</span>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $subscription->plan->name }} ({{ $subscription->billing_interval }})</td>
                    <td class="px-4 py-3 text-slate-600">{{ $subscription->gateway_reference }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $subscription->updated_at->format('d.m.Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2 justify-end">
                            <form method="POST" action="{{ route('admin.subscriptions.approve', $subscription) }}">
                                @csrf
                                <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">{{ __('Approve') }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.subscriptions.reject', $subscription) }}">
                                @csrf
                                <button class="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">{{ __('Reject') }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">{{ __('No pending bank transfers.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="text-lg font-semibold text-slate-800 mb-3">{{ __('Recent subscriptions') }}</h2>
    <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
            <tr>
                <th class="px-4 py-3">{{ __('Buyer') }}</th>
                <th class="px-4 py-3">{{ __('Plan') }}</th>
                <th class="px-4 py-3">{{ __('Method') }}</th>
                <th class="px-4 py-3">{{ __('Status') }}</th>
                <th class="px-4 py-3">{{ __('Valid until') }}</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($recent as $subscription)
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $subscription->billable?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $subscription->plan->name }}</td>
                    <td class="px-4 py-3 capitalize text-slate-600">{{ $subscription->payment_method }}</td>
                    <td class="px-4 py-3 capitalize text-slate-600">{{ $subscription->status }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $subscription->ends_at?->format('d.m.Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">{{ __('No subscriptions yet.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
