@extends('layouts.shell', ['title' => $customer->name])

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('SweetSpot') }}</p>
            <h1 class="text-3xl font-bold text-slate-900">{{ $customer->name }}</h1>
            <p class="text-sm text-slate-500">{{ $customer->industry ?? '-' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('sweetspot.customers.edit', $customer) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Edit') }}</a>
            <a href="{{ route('sweetspot.customers.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Back') }}</a>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-3">
        @foreach ([
            ['label' => __('Total score'), 'value' => number_format($customer->score->total_score ?? 0, 2), 'top' => $customer->score?->top_flag, 'color' => 'text-indigo-600'],
            ['label' => __('Rank'), 'value' => '#'.($customer->score->rank ?? '-'), 'top' => false, 'color' => 'text-slate-900'],
            ['label' => __('Margin per hour'), 'value' => '€'.number_format($customer->score->margin_per_hour ?? 0, 2), 'top' => false, 'color' => 'text-emerald-600'],
        ] as $card)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs uppercase text-slate-500">{{ $card['label'] }}</div>
                <div class="mt-1 text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
                @if ($card['top'])
                    <span class="mt-2 inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">{{ __('Top 20%') }}</span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Customer data') }}</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Industry') }}</dt><dd class="font-medium text-slate-900">{{ $customer->industry ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Company size') }}</dt><dd class="font-medium text-slate-900">{{ $customer->company_size ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Revenue') }}</dt><dd class="font-medium text-slate-900">€{{ number_format($customer->revenue ?? 0, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Profit margin') }}</dt><dd class="font-medium text-slate-900">€{{ number_format($customer->profit_margin_eur ?? 0, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Effort hours') }}</dt><dd class="font-medium text-slate-900">{{ $customer->effort_hours ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Repeat rate') }}</dt><dd class="font-medium text-slate-900">{{ $customer->repeat_rate ?? '-' }}%</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Recommendations') }}</dt><dd class="font-medium text-slate-900">{{ $customer->recommendations ?? '-' }}</dd></div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">{{ __('Score breakdown') }}</h2>
            @if ($customer->score)
                <dl class="space-y-3 text-sm">
                    @foreach ([
                        ['label' => __('Profitability'), 'value' => $customer->score->profitability_score],
                        ['label' => __('Effort'), 'value' => $customer->score->effort_score],
                        ['label' => __('Chemistry'), 'value' => $customer->score->chemistry_score],
                        ['label' => __('Growth'), 'value' => $customer->score->growth_score],
                        ['label' => __('Repeat'), 'value' => $customer->score->repeat_score],
                        ['label' => __('Recommendation'), 'value' => $customer->score->recommendation_score],
                        ['label' => __('Payment'), 'value' => $customer->score->payment_score],
                    ] as $row)
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">{{ $row['label'] }}</dt>
                            <dd class="font-semibold text-slate-900">{{ number_format($row['value'], 2) }}</dd>
                        </div>
                    @endforeach
                </dl>
            @else
                <p class="text-sm text-slate-500">{{ __('No score calculated yet.') }}</p>
            @endif
        </div>
    </div>

    @if ($customer->notes)
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-2 text-lg font-semibold text-slate-900">{{ __('Notes') }}</h2>
            <p class="whitespace-pre-line text-sm text-slate-700">{{ $customer->notes }}</p>
        </div>
    @endif
</div>
@endsection
