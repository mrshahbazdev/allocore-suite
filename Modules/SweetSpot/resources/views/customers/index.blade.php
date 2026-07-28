@extends('layouts.shell', ['title' => __('SweetSpot Customers')])

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('SweetSpot') }}</p>
            <h1 class="text-3xl font-bold text-slate-900">{{ __('Customers') }}</h1>
        </div>
        <a href="{{ route('sweetspot.customers.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Add customer') }}</a>
    </div>

    @if ($customers->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-slate-500 shadow-sm">
            <p class="font-semibold">{{ __('No customers yet') }}</p>
            <p class="mt-1 text-sm">{{ __('Add your first customer to see scoring.') }}</p>
            <a href="{{ route('sweetspot.customers.create') }}" class="mt-4 inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Add customer') }}</a>
        </div>
    @else
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">{{ __('Name') }}</th>
                            <th class="px-5 py-3">{{ __('Industry') }}</th>
                            <th class="px-5 py-3">{{ __('Revenue') }}</th>
                            <th class="px-5 py-3">{{ __('Margin/h') }}</th>
                            <th class="px-5 py-3">{{ __('Score') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($customers as $customer)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-900">{{ $customer->name }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $customer->industry ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-600">€{{ number_format($customer->revenue, 2) }}</td>
                                <td class="px-5 py-3 text-slate-600">€{{ number_format($customer->score->margin_per_hour ?? 0, 2) }}</td>
                                <td class="px-5 py-3">
                                    @php($score = $customer->score->total_score ?? 0)
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $score >= 80 ? 'bg-emerald-100 text-emerald-700' : ($score >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                        {{ number_format($score, 2) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-sm">
                                    <a href="{{ route('sweetspot.customers.show', $customer) }}" class="text-slate-600 hover:text-slate-900">{{ __('View') }}</a>
                                    <a href="{{ route('sweetspot.customers.edit', $customer) }}" class="ml-3 text-indigo-600 hover:text-indigo-500">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('sweetspot.customers.destroy', $customer) }}" class="ml-3 inline" onsubmit="return confirm('{{ __('Delete this customer?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-800">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-5 py-3">{{ $customers->links() }}</div>
        </div>
    @endif
</div>
@endsection
