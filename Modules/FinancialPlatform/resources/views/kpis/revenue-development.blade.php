@extends('layouts.shell')

@section('title', 'Revenue Development — Allocore')
@section('page-title', 'Revenue Development KPI')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ __('Revenue Development') }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Actual sales / target sales in %.') }}</p>
                </div>
                <a href="{{ url('app/finance') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to dashboard') }}</a>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('Target sales') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($revenueDevelopment['targetSales'] ?? 0, 2) }}</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('Actual sales') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($revenueDevelopment['actualSales'] ?? 0, 2) }}</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('Achievement') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($revenueDevelopment['percentage'] ?? 0, 1) }}%</div>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div class="text-sm font-semibold text-slate-900">{{ __('Formula') }}</div>
                <div class="mt-1 text-sm text-slate-600">{{ __('actual_sales / target_sales × 100') }}</div>
                <div class="mt-3 text-sm text-slate-500">
                    Source: {{ $revenueDevelopment['sourceLabel'] ?? 'InvoiceMaker' }}
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Configure KPI') }}</h2>

            <form method="POST" action="{{ route('financialplatform.revenue-development.update') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Target sales') }}</label>
                    <input type="number" step="0.01" min="0" name="target_sales" value="{{ old('target_sales', $settings['target_sales'] ?? 0) }}" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Actual source') }}</label>
                    <select name="actual_source" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (['analysis' => 'Financial analyses', 'invoicemaker' => 'InvoiceMaker', 'seostory' => 'SeoStory financial analyses', 'manual' => 'Manual entry'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('actual_source', $settings['actual_source'] ?? 'invoicemaker') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('SeoStory actual revenue') }}</label>
                    <input type="number" step="0.01" min="0" name="seostory_revenue" value="{{ old('seostory_revenue', $settings['seostory_revenue'] ?? '') }}" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-slate-500">{{ __('Manual fallback until SeoStory API is configured.') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Manual actual sales') }}</label>
                    <input type="number" step="0.01" min="0" name="actual_manual" value="{{ old('actual_manual', $settings['actual_manual'] ?? '') }}" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <button class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Save KPI') }}</button>
            </form>
        </div>
    </div>
@endsection
