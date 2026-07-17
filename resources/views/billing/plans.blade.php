@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Plans & Pricing') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Subscribe to the tools you need — pay with card, PayPal, or bank transfer.') }}</p>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($plans as $plan)
            @php($highlight = $highlightModule && $plan->modules->contains('key', $highlightModule))
            <div class="rounded-2xl bg-white border {{ $highlight ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-slate-200' }} p-6 shadow-sm flex flex-col">
                <h3 class="text-lg font-semibold text-slate-900">{{ $plan->name }}</h3>
                @if ($plan->description)
                    <p class="mt-1 text-sm text-slate-500">{{ $plan->description }}</p>
                @endif
                <div class="mt-4">
                    <span class="text-3xl font-bold text-slate-900">{{ number_format($plan->price_monthly, 2) }}</span>
                    <span class="text-sm text-slate-500">{{ $plan->currency }} / {{ __('month') }}</span>
                    <div class="text-xs text-slate-400">{{ __('or') }} {{ number_format($plan->price_yearly, 2) }} {{ $plan->currency }} / {{ __('year') }}</div>
                </div>
                <ul class="mt-4 space-y-1 text-sm text-slate-600 flex-1">
                    @foreach ($plan->modules as $module)
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            {{ $module->name }}
                        </li>
                    @endforeach
                </ul>

                <form method="POST" action="{{ route('billing.checkout', $plan) }}" class="mt-6 space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        <select name="interval" class="rounded-lg border-slate-300 text-sm">
                            <option value="monthly">{{ __('Monthly') }}</option>
                            <option value="yearly">{{ __('Yearly') }}</option>
                        </select>
                        <select name="billable" class="rounded-lg border-slate-300 text-sm">
                            @if (in_array($plan->billable_scope, ['user', 'both']))
                                <option value="user">{{ __('For me') }}</option>
                            @endif
                            @if (in_array($plan->billable_scope, ['team', 'both']))
                                <option value="team">{{ __('For my team') }}</option>
                            @endif
                        </select>
                    </div>
                    <select name="payment_method" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="stripe">{{ __('Credit Card (Stripe)') }}</option>
                        <option value="paypal">PayPal</option>
                        <option value="bank">{{ __('Bank Transfer') }}</option>
                    </select>
                    <input type="text" name="coupon_code" placeholder="{{ __('Coupon code') }}" class="w-full rounded-lg border-slate-300 text-sm">
                    <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        {{ __('Subscribe') }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endsection
