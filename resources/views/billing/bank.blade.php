@extends('layouts.shell')

@section('content')
    <div class="max-w-xl">
        <h1 class="text-2xl font-bold text-slate-900 mb-2">{{ __('Bank Transfer') }}</h1>
        <p class="text-sm text-slate-500 mb-6">
            {{ __('Transfer the amount below and submit your payment reference. Your subscription will be activated after our team verifies the payment.') }}
        </p>

        <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm mb-6 text-sm">
            <dl class="space-y-2">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Plan') }}</dt><dd class="font-medium">{{ $subscription->plan->name }} ({{ $subscription->billing_interval }})</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Amount') }}</dt><dd class="font-medium">{{ number_format($subscription->plan->priceFor($subscription->billing_interval), 2) }} {{ $subscription->plan->currency }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Account holder') }}</dt><dd class="font-medium">{{ config('services.bank.account_holder') ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">IBAN</dt><dd class="font-medium">{{ config('services.bank.iban') ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">BIC</dt><dd class="font-medium">{{ config('services.bank.bic') ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Bank') }}</dt><dd class="font-medium">{{ config('services.bank.bank_name') ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Payment reference') }}</dt><dd class="font-medium">SUB-{{ $subscription->id }}</dd></div>
            </dl>
        </div>

        <form method="POST" action="{{ route('billing.bank.submit', $subscription) }}" enctype="multipart/form-data"
              class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Your transfer reference') }}</label>
                <input type="text" name="reference" required class="w-full rounded-lg border-slate-300 text-sm" placeholder="SUB-{{ $subscription->id }}">
                @error('reference')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Payment receipt (optional)') }}</label>
                <input type="file" name="receipt" class="w-full text-sm">
                @error('receipt')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                {{ __('Submit for approval') }}
            </button>
        </form>
    </div>
@endsection
