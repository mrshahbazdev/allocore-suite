@extends('layouts.shell', ['title' => __('SweetSpot Dashboard')])

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('SweetSpot') }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('sweetspot.settings.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Scoring weights') }}</a>
            <form method="POST" action="{{ route('sweetspot.recalculate') }}">
                @csrf
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Recalculate scores') }}</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Customers') }}</div>
            <div class="text-3xl font-bold text-indigo-600">{{ $customerCount }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Average score') }}</div>
            <div class="text-3xl font-bold text-emerald-600">{{ number_format($averageScore ?? 0, 2) }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Top 20%') }}</div>
            <div class="text-3xl font-bold text-amber-600">{{ $topCustomers->where('top_flag', true)->count() }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-slate-500">{{ __('Last calculated') }}</div>
            <div class="text-lg font-bold text-slate-700">{{ $calculatedAt ? \Illuminate\Support\Carbon::parse($calculatedAt)->diffForHumans() : '-' }}</div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Top customers') }}</h2>
            <a href="{{ route('sweetspot.customers.index') }}" class="text-sm text-indigo-600 hover:underline">{{ __('View all') }}</a>
        </div>

        @if ($topCustomers->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No customers yet. Add customers and recalculate scores.') }}</p>
        @else
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ __('Rank') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Customer') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Margin/h') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Total score') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Top') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($topCustomers as $score)
                        <tr>
                            <td class="px-4 py-3 text-slate-500">#{{ $score->rank }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">
                                <a href="{{ route('sweetspot.customers.show', $score->customer) }}" class="hover:text-indigo-600">{{ $score->customer->name }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">€{{ number_format($score->margin_per_hour, 2) }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ number_format($score->total_score, 2) }}</td>
                            <td class="px-4 py-3">
                                @if ($score->top_flag)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">{{ __('Top') }}</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
