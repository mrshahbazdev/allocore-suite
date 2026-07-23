@extends('layouts.shell', ['title' => __('SweetSpot Customers')])

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Customers') }}</h1>
        <a href="{{ route('sweetspot.customers.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add customer') }}</a>
    </div>

    @if ($customers->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">{{ __('No customers yet.') }}</div>
    @else
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('Name') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Industry') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Revenue') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Margin/h') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('Score') }}</th>
                        <th class="px-5 py-3 font-medium text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($customers as $customer)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $customer->name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $customer->industry ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-600">€{{ number_format($customer->revenue, 2) }}</td>
                            <td class="px-5 py-3 text-slate-600">€{{ number_format($customer->score->margin_per_hour ?? 0, 2) }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ number_format($customer->score->total_score ?? 0, 2) }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('sweetspot.customers.show', $customer) }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('View') }}</a>
                                <a href="{{ route('sweetspot.customers.edit', $customer) }}" class="ml-3 text-sm text-indigo-600 hover:text-indigo-500">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('sweetspot.customers.destroy', $customer) }}" class="ml-3 inline" onsubmit="return confirm('{{ __('Delete this customer?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-rose-600 hover:text-rose-800">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3">{{ $customers->links() }}</div>
        </div>
    @endif
</div>
@endsection
