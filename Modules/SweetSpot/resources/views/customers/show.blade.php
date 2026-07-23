@extends('layouts.shell', ['title' => $customer->name])

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-6 flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-slate-900">{{ $customer->name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('sweetspot.customers.edit', $customer) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
            <a href="{{ route('sweetspot.customers.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Back') }}</a>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Total score') }}</div>
            <div class="text-3xl font-bold text-indigo-600">{{ number_format($customer->score->total_score ?? 0, 2) }}</div>
            @if ($customer->score?->top_flag)
                <span class="mt-2 inline-block rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">{{ __('Top 20%') }}</span>
            @endif
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Rank') }}</div>
            <div class="text-3xl font-bold text-slate-900">#{{ $customer->score->rank ?? '-' }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Margin per hour') }}</div>
            <div class="text-3xl font-bold text-emerald-600">€{{ number_format($customer->score->margin_per_hour ?? 0, 2) }}</div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 md:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-3 text-lg font-semibold text-slate-900">{{ __('Customer data') }}</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Industry') }}</dt><dd class="font-medium text-slate-900">{{ $customer->industry ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Company size') }}</dt><dd class="font-medium text-slate-900">{{ $customer->company_size ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Revenue') }}</dt><dd class="font-medium text-slate-900">€{{ number_format($customer->revenue ?? 0, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Profit margin') }}</dt><dd class="font-medium text-slate-900">€{{ number_format($customer->profit_margin_eur ?? 0, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Effort hours') }}</dt><dd class="font-medium text-slate-900">{{ $customer->effort_hours ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Repeat rate') }}</dt><dd class="font-medium text-slate-900">{{ $customer->repeat_rate ?? '-' }}%</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Recommendations') }}</dt><dd class="font-medium text-slate-900">{{ $customer->recommendations ?? '-' }}</dd></div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-3 text-lg font-semibold text-slate-900">{{ __('Score breakdown') }}</h2>
            @if ($customer->score)
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Profitability') }}</dt><dd class="font-medium text-slate-900">{{ number_format($customer->score->profitability_score, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Effort') }}</dt><dd class="font-medium text-slate-900">{{ number_format($customer->score->effort_score, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Chemistry') }}</dt><dd class="font-medium text-slate-900">{{ number_format($customer->score->chemistry_score, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Growth') }}</dt><dd class="font-medium text-slate-900">{{ number_format($customer->score->growth_score, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Repeat') }}</dt><dd class="font-medium text-slate-900">{{ number_format($customer->score->repeat_score, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Recommendation') }}</dt><dd class="font-medium text-slate-900">{{ number_format($customer->score->recommendation_score, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Payment') }}</dt><dd class="font-medium text-slate-900">{{ number_format($customer->score->payment_score, 2) }}</dd></div>
                </dl>
            @else
                <p class="text-sm text-slate-500">{{ __('No score calculated yet.') }}</p>
            @endif
        </div>
    </div>

    @if ($customer->notes)
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-2 text-lg font-semibold text-slate-900">{{ __('Notes') }}</h2>
            <p class="whitespace-pre-line text-sm text-slate-700">{{ $customer->notes }}</p>
        </div>
    @endif
</div>
@endsection
