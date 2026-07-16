@extends('layouts.shell')

@section('title', 'Deep KPIs — Allocore')
@section('page-title', 'Deep KPIs')

@php
$revenue = $deepKpis['revenue'] ?? [];
$settings = $settings ?? [];
@endphp

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Deep KPIs</h1>
                        <p class="mt-1 text-sm text-slate-500">Revenue, profit, order, influence and legacy metrics aligned with the Business Readiness framework.</p>
                    </div>
                    <a href="{{ url('app/finance') }}" class="text-sm font-medium text-indigo-600 hover:underline">Back to dashboard</a>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Revenue</h2>

                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm font-semibold text-slate-900">Umsatzbedarf</div>
                    <div class="mt-3 grid gap-4 sm:grid-cols-4">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">Target sales</div>
                            <div class="mt-1 text-xl font-semibold text-slate-900">{{ number_format($revenue['umsatzbedarf']['target'] ?? 0, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">Actual sales</div>
                            <div class="mt-1 text-xl font-semibold text-slate-900">{{ number_format($revenue['umsatzbedarf']['actual'] ?? 0, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">Target / Actual</div>
                            <div class="mt-1 text-xl font-semibold text-slate-900">{{ $revenue['umsatzbedarf']['ratio'] !== null ? number_format($revenue['umsatzbedarf']['ratio'], 4) : '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">Achievement</div>
                            <div class="mt-1 text-xl font-semibold text-slate-900">{{ number_format($revenue['umsatzbedarf']['achievement'] ?? 0, 1) }}%</div>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-500">
                        Source: {{ $revenue['umsatzbedarf']['sourceLabel'] ?? 'InvoiceMaker' }}
                        <span class="ml-2 inline-flex rounded px-2 py-0.5 text-xs font-medium {{ match($revenue['umsatzbedarf']['status'] ?? 'neutral') { 'green' => 'bg-green-100 text-green-700', 'yellow' => 'bg-yellow-100 text-yellow-700', 'red' => 'bg-red-100 text-red-700', default => 'bg-slate-100 text-slate-600' } }}">{{ ucfirst($revenue['umsatzbedarf']['status'] ?? 'neutral') }}</span>
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm font-semibold text-slate-900">Leadqualität</div>
                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                                    <th class="pb-2 pr-4">KPI</th>
                                    <th class="pb-2 pr-4 text-right">Current month</th>
                                    <th class="pb-2 pr-4 text-right">Previous month</th>
                                    <th class="pb-2 pr-4 text-right">Ratio</th>
                                    <th class="pb-2 pr-4 text-right">Change</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($revenue['leadQuality'] ?? [] as $key => $metric)
                                    <tr>
                                        <td class="py-2 pr-4 font-medium text-slate-700">{{ $metric['label'] }}</td>
                                        <td class="py-2 pr-4 text-right">{{ number_format($metric['current'], 2) }}</td>
                                        <td class="py-2 pr-4 text-right">{{ number_format($metric['previous'], 2) }}</td>
                                        <td class="py-2 pr-4 text-right">{{ $metric['ratio'] !== null ? number_format($metric['ratio'], 4) : '—' }}</td>
                                        <td class="py-2 pr-4 text-right">{{ $metric['changePercent'] !== null ? number_format($metric['changePercent'], 1).'%' : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-sm font-semibold text-slate-900">Abschlussquote</div>
                        <div class="mt-3 grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500">Current</div>
                                <div class="mt-1 text-lg font-semibold text-slate-900">{{ ($revenue['abschlussquote']['conversionRateCurrent'] ?? null) !== null ? number_format($revenue['abschlussquote']['conversionRateCurrent'], 1).'%' : '—' }}</div>
                                <div class="text-xs text-slate-500">{{ $revenue['abschlussquote']['newCustomersCurrent'] ?? 0 }} / {{ $revenue['abschlussquote']['leadsCurrent'] ?? 0 }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500">Previous</div>
                                <div class="mt-1 text-lg font-semibold text-slate-900">{{ ($revenue['abschlussquote']['conversionRatePrevious'] ?? null) !== null ? number_format($revenue['abschlussquote']['conversionRatePrevious'], 1).'%' : '—' }}</div>
                                <div class="text-xs text-slate-500">{{ $revenue['abschlussquote']['newCustomersPrevious'] ?? 0 }} / {{ $revenue['abschlussquote']['leadsPrevious'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-sm font-semibold text-slate-900">Vertragstreue Kunden</div>
                        <div class="mt-3 grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500">Avg. days to payment</div>
                                <div class="mt-1 text-lg font-semibold text-slate-900">{{ ($revenue['vertragstreue']['averageDaysCurrent'] ?? null) !== null ? number_format($revenue['vertragstreue']['averageDaysCurrent'], 1) : '—' }}</div>
                                <div class="text-xs text-slate-500">{{ $revenue['vertragstreue']['invoicesCurrent'] ?? 0 }} invoices</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500">Previous month</div>
                                <div class="mt-1 text-lg font-semibold text-slate-900">{{ ($revenue['vertragstreue']['averageDaysPrevious'] ?? null) !== null ? number_format($revenue['vertragstreue']['averageDaysPrevious'], 1) : '—' }}</div>
                                <div class="text-xs text-slate-500">{{ $revenue['vertragstreue']['invoicesPrevious'] ?? 0 }} invoices</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (['profit' => 'Profit', 'order' => 'Order', 'influence' => 'Influence', 'legacy' => 'Legacy'] as $phase => $label)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-900">{{ $label }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ $deepKpis[$phase]['note'] ?? 'Concrete KPIs are not defined for this pillar yet.' }}</p>
                        <a href="{{ url('app/audit') }}" class="mt-3 inline-flex text-sm font-medium text-indigo-600 hover:underline">Open AuditPro</a>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Configure Deep KPIs</h2>

            <form method="POST" action="{{ route('financialplatform.deep-kpis.update') }}" class="mt-4 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700">Target sales</label>
                    <input type="number" step="0.01" min="0" name="target_sales" value="{{ old('target_sales', $settings['target_sales'] ?? 0) }}" class="mt-1 w-full rounded-lg border-slate-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Actual source</label>
                    <select name="actual_source" class="mt-1 w-full rounded-lg border-slate-300">
                        @foreach (['analysis' => 'Financial analyses', 'invoicemaker' => 'InvoiceMaker', 'seostory' => 'SeoStory financial analyses', 'manual' => 'Manual entry'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('actual_source', $settings['actual_source'] ?? 'invoicemaker') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">SeoStory actual revenue</label>
                    <input type="number" step="0.01" min="0" name="seostory_revenue" value="{{ old('seostory_revenue', $settings['seostory_revenue'] ?? '') }}" class="mt-1 w-full rounded-lg border-slate-300">
                    <p class="mt-1 text-xs text-slate-500">Manual fallback until SeoStory API is configured.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Manual actual sales</label>
                    <input type="number" step="0.01" min="0" name="actual_manual" value="{{ old('actual_manual', $settings['actual_manual'] ?? '') }}" class="mt-1 w-full rounded-lg border-slate-300">
                </div>

                <div class="border-t border-slate-200 pt-4">
                    <div class="text-sm font-semibold text-slate-900">Lead quality metrics</div>
                    <p class="text-xs text-slate-500">Enter current and previous month values. CTR will be calculated from clicks/impressions when left empty.</p>

                    @foreach (['impressions' => 'Impressions', 'clicks' => 'Clicks', 'ctr' => 'CTR (%)', 'average_position' => 'Avg. Google position', 'page_value' => 'Page value'] as $key => $label)
                        <div class="mt-3 grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-700">{{ $label }} — current</label>
                                <input type="number" step="0.01" min="0" name="metric_{{ $key }}_current" value="{{ old('metric_'.$key.'_current', $settings['metrics'][$key.'_current'] ?? '') }}" class="mt-1 w-full rounded-lg border-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700">{{ $label }} — previous</label>
                                <input type="number" step="0.01" min="0" name="metric_{{ $key }}_previous" value="{{ old('metric_'.$key.'_previous', $settings['metrics'][$key.'_previous'] ?? '') }}" class="mt-1 w-full rounded-lg border-slate-300">
                            </div>
                        </div>
                    @endforeach
                </div>

                <button class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Deep KPIs</button>
            </form>
        </div>
    </div>
@endsection
