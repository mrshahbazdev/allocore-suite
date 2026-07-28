@extends('layouts.shell', ['title' => __('SweetSpot Dashboard')])

@section('content')
<div class="mx-auto max-w-7xl space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('SweetSpot') }}</p>
            <h1 class="text-3xl font-bold text-slate-900">{{ __('Customer scoring') }}</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('sweetspot.settings.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Scoring weights') }}</a>
            <form method="POST" action="{{ route('sweetspot.recalculate') }}">
                @csrf
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Recalculate scores') }}</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        @foreach ([
            ['label' => __('Customers'), 'value' => $customerCount, 'icon' => 'M15 19.128a9.38 9.38 0 002.625.364 11.978 11.978 0 00-2.625-.364C10.444 18.626 7.647 17.13 6 15m12 0c-.816.78-1.85 1.24-3 1.382A9.38 9.38 0 0012.375 15c-2.474 0-4.75.81-6.527 2.181C4.178 17.61 3.5 17.107 3.5 16.5v-.75a2.25 2.25 0 012.25-2.25h12a2.25 2.25 0 012.25 2.25v.75c0 .607-.678 1.11-1.348.681A9.38 9.38 0 0015 19.128z M12 11.25a3 3 0 100-6 3 3 0 000 6z', 'color' => 'text-indigo-600'],
            ['label' => __('Average score'), 'value' => number_format($averageScore ?? 0, 2), 'icon' => 'M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z M13.5 10.5H21a7.5 7.5 0 00-7.5-7.5v7.5z', 'color' => 'text-emerald-600'],
            ['label' => __('Top 20%'), 'value' => $topCustomers->where('top_flag', true)->count(), 'icon' => 'M9.813 15.904A5.997 5.997 0 006 12.632V8.25a8.25 8.25 0 0116.5 0v4.382a5.997 5.997 0 01-3.813 5.572l-3.375 1.124A2.25 2.25 0 0112 21.75v-.997a5.06 5.06 0 00-2.187-1.849zM10.5 8.25v4.382a3.747 3.747 0 002.385 3.481l3.375 1.124V21.75h1.5v-1.884l3.375-1.124A3.747 3.747 0 0021.75 12.633V8.25a6.75 6.75 0 00-13.5 0z', 'color' => 'text-amber-600'],
            ['label' => __('Last calculated'), 'value' => $calculatedAt ? \Illuminate\Support\Carbon::parse($calculatedAt)->diffForHumans() : '-', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-slate-600'],
        ] as $card)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="text-xs uppercase text-slate-500">{{ $card['label'] }}</div>
                    <svg class="h-5 w-5 {{ $card['color'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/></svg>
                </div>
                <div class="mt-1 text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Top customers') }}</h2>
            <a href="{{ route('sweetspot.customers.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">{{ __('View all') }} →</a>
        </div>

        @if ($topCustomers->isEmpty())
            <div class="rounded-lg border border-dashed border-slate-300 p-10 text-center text-slate-600">
                <p class="font-semibold">{{ __('No customers yet') }}</p>
                <p class="mt-1 text-sm">{{ __('Add customers and recalculate scores.') }}</p>
                <a href="{{ route('sweetspot.customers.create') }}" class="mt-4 inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Add customer') }}</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('Rank') }}</th>
                            <th class="px-4 py-3">{{ __('Customer') }}</th>
                            <th class="px-4 py-3">{{ __('Margin/h') }}</th>
                            <th class="px-4 py-3">{{ __('Total score') }}</th>
                            <th class="px-4 py-3">{{ __('Top') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($topCustomers as $score)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-500">#{{ $score->rank }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    <a href="{{ route('sweetspot.customers.show', $score->customer) }}" class="text-indigo-600 hover:text-indigo-500">{{ $score->customer->name }}</a>
                                </td>
                                <td class="px-4 py-3 text-slate-600">€{{ number_format($score->margin_per_hour, 2) }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ number_format($score->total_score, 2) }}</td>
                                <td class="px-4 py-3">
                                    @if ($score->top_flag)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">{{ __('Top') }}</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
