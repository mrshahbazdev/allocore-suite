@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.tax_rates.title') }}</h1>
            <p class="text-sm text-slate-500">{{ __('admin.tax_rates.description') }}</p>
        </div>
        <a href="{{ route('admin.tax-rates.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.tax_rates.create_button') }}</a>
    </div>

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.tax-rates.index') }}" class="flex gap-2 p-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.tax_rates.search_placeholder') }}" class="flex-1 rounded-lg border-slate-300 text-sm">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Search') }}</button>
        </form>

        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('admin.tax_rates.rate') }}</th>
                    <th class="px-4 py-3">{{ __('Country') }}</th>
                    <th class="px-4 py-3">{{ __('Region') }}</th>
                    <th class="px-4 py-3">{{ __('Default') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($taxRates as $taxRate)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $taxRate->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ number_format($taxRate->rate, 2) }}%</td>
                        <td class="px-4 py-3 text-slate-600">{{ $taxRate->country ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $taxRate->region ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $taxRate->is_default ? __('Yes') : __('No') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $taxRate->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $taxRate->is_active ? __('Active') : __('Inactive') }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.tax-rates.edit', $taxRate) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.tax-rates.destroy', $taxRate) }}" onsubmit="return confirm('{{ __('admin.tax_rates.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">{{ __('admin.tax_rates.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $taxRates->links() }}</div>
@endsection
