@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.subscriptions.title', ['name' => $user->name]) }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.subscriptions.description') }}</p>
        </div>
        <a href="{{ route('admin.users.show', $user) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('admin.users.back_to_user') }}</a>
    </div>

    <div class="mb-8 overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('admin.subscriptions.add') }}</h2>
        <form method="POST" action="{{ route('admin.users.subscriptions.store', $user) }}" class="grid gap-5 md:grid-cols-3">
            @csrf

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700">{{ __('Plan') }}</label>
                <select name="plan_id" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('admin.subscriptions.select_plan') }}</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }} ({{ $plan->modules->pluck('name')->implode(', ') }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('admin.subscriptions.interval') }}</label>
                <select name="billing_interval" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="monthly" @selected(old('billing_interval') === 'monthly')>{{ __('admin.subscriptions.monthly') }}</option>
                    <option value="yearly" @selected(old('billing_interval') === 'yearly')>{{ __('admin.subscriptions.yearly') }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('admin.subscriptions.method') }}</label>
                <select name="payment_method" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="manual" @selected(old('payment_method') === 'manual')>{{ __('admin.subscriptions.manual') }}</option>
                    <option value="bank" @selected(old('payment_method') === 'bank')>{{ __('admin.subscriptions.bank') }}</option>
                    <option value="stripe" @selected(old('payment_method') === 'stripe')>{{ __('admin.subscriptions.stripe') }}</option>
                    <option value="paypal" @selected(old('payment_method') === 'paypal')>{{ __('admin.subscriptions.paypal') }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('admin.subscriptions.status') }}</label>
                <select name="status" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="active" @selected(old('status') === 'active')>{{ __('admin.subscriptions.active') }}</option>
                    <option value="pending" @selected(old('status') === 'pending')>{{ __('admin.subscriptions.pending') }}</option>
                    <option value="cancelled" @selected(old('status') === 'cancelled')>{{ __('admin.subscriptions.cancelled') }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('admin.subscriptions.starts_at') }}</label>
                <input name="starts_at" type="date" value="{{ old('starts_at') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">{{ __('admin.subscriptions.ends_at') }}</label>
                <input name="ends_at" type="date" value="{{ old('ends_at') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-slate-700">{{ __('admin.subscriptions.note') }}</label>
                <input name="admin_note" value="{{ old('admin_note') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.subscriptions.add_button') }}</button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Plan') }}</th>
                    <th class="px-4 py-3">{{ __('admin.subscriptions.interval') }}</th>
                    <th class="px-4 py-3">{{ __('admin.subscriptions.method') }}</th>
                    <th class="px-4 py-3">{{ __('admin.subscriptions.status') }}</th>
                    <th class="px-4 py-3">{{ __('admin.subscriptions.valid') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($user->toolSubscriptions as $subscription)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $subscription->plan?->name ?? '—' }}</td>
                        <td class="px-4 py-3 capitalize text-slate-600">{{ $subscription->billing_interval }}</td>
                        <td class="px-4 py-3 capitalize text-slate-600">{{ $subscription->payment_method }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $subscription->isActive() ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                {{ $subscription->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $subscription->ends_at?->format('d.m.Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @if ($subscription->status === 'pending')
                                    <form method="POST" action="{{ route('admin.users.subscriptions.approve', [$user, $subscription]) }}">
                                        @csrf
                                        <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">{{ __('Approve') }}</button>
                                    </form>
                                @endif
                                @if (in_array($subscription->status, ['active', 'pending']))
                                    <form method="POST" action="{{ route('admin.users.subscriptions.cancel', [$user, $subscription]) }}" onsubmit="return confirm('{{ __('admin.subscriptions.confirm_cancel') }}')">
                                        @csrf
                                        <button class="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">{{ __('Cancel') }}</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.users.subscriptions.destroy', [$user, $subscription]) }}" onsubmit="return confirm('{{ __('admin.subscriptions.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">{{ __('admin.subscriptions.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
