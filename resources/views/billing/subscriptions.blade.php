@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('My Subscriptions') }}</h1>
    </div>

    @foreach ([['title' => __('Personal'), 'items' => $subscriptions], ['title' => __('Team'), 'items' => $teamSubscriptions]] as $group)
        <h2 class="text-lg font-semibold text-slate-800 mb-3">{{ $group['title'] }}</h2>
        <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden mb-8">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Plan') }}</th>
                    <th class="px-4 py-3">{{ __('Tools') }}</th>
                    <th class="px-4 py-3">{{ __('Method') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Valid until') }}</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($group['items'] as $subscription)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $subscription->plan->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $subscription->plan->modules->pluck('name')->join(', ') }}</td>
                        <td class="px-4 py-3 capitalize text-slate-600">{{ $subscription->payment_method }}</td>
                        <td class="px-4 py-3">
                            @php($colors = ['active' => 'bg-emerald-100 text-emerald-700', 'pending' => 'bg-amber-100 text-amber-700', 'cancelled' => 'bg-slate-100 text-slate-500', 'expired' => 'bg-slate-100 text-slate-500', 'rejected' => 'bg-rose-100 text-rose-700'])
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $colors[$subscription->status] ?? '' }}">{{ ucfirst($subscription->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $subscription->ends_at?->format('d.m.Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">{{ __('No subscriptions yet.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endforeach
@endsection
