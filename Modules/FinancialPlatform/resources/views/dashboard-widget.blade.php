<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="text-sm font-medium text-slate-500">{{ __('FinancialPlatform') }}</div>
            <h3 class="mt-1 text-lg font-semibold text-slate-900">{{ __('Corporate Maturity') }}</h3>
        </div>
        <a href="{{ url('app/finance') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Open') }}</a>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-xs text-slate-500">{{ __('Companies') }}</div>
            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $companies }}</div>
        </div>
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-xs text-slate-500">{{ __('Analyses') }}</div>
            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $analyses }}</div>
        </div>
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-xs text-slate-500">{{ __('Leads') }}</div>
            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $leads }}</div>
        </div>
        <div class="rounded-xl bg-slate-50 p-3">
            <div class="text-xs text-slate-500">{{ __('Revenue') }}</div>
            <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($revenue, 0) }}</div>
        </div>
    </div>

    <div class="mt-4 rounded-xl bg-indigo-50 px-4 py-3">
        <div class="text-xs font-medium uppercase tracking-wide text-indigo-600">{{ __('Corporate Maturity') }}</div>
        <div class="mt-1 text-2xl font-semibold text-indigo-900">{{ number_format($maturity, 1) }}/100</div>
    </div>

    @php($deepKpis = $deepKpis ?? app(\Modules\FinancialPlatform\Services\DeepKpiSnapshot::class)->forTeam(auth()->user()?->currentTeam))
    @php($revenueDevelopment = $deepKpis['revenue']['umsatzbedarf'] ?? [])

    <details class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
        <summary class="cursor-pointer list-none text-sm font-semibold text-slate-900">
            {{ __('Business Readiness') }}
        </summary>

        <div class="mt-4 space-y-3">
            <details class="rounded-lg border border-slate-200 bg-white p-3" open>
                <summary class="cursor-pointer list-none text-sm font-semibold text-slate-800">
                    {{ __('Revenue') }}
                </summary>

                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Target sales') }}</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">
                            {{ number_format($revenueDevelopment['target'] ?? 0, 2) }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Actual sales') }}</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">
                            {{ number_format($revenueDevelopment['actual'] ?? 0, 2) }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Achievement') }}</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">
                            {{ number_format($revenueDevelopment['achievement'] ?? 0, 1) }}%
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-xs text-slate-500">
                    {{ __('Source') }}: {{ $revenueDevelopment['sourceLabel'] ?? __('InvoiceMaker') }}
                    · {{ __('Status') }}:
                    <span class="font-medium text-slate-700">{{ ucfirst($revenueDevelopment['status'] ?? 'neutral') }}</span>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Abschlussquote') }}</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">
                            {{ ($deepKpis['revenue']['abschlussquote']['conversionRateCurrent'] ?? null) !== null ? number_format($deepKpis['revenue']['abschlussquote']['conversionRateCurrent'], 1).'%' : '—' }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Vertragstreue') }}</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">
                            {{ ($deepKpis['revenue']['vertragstreue']['averageDaysCurrent'] ?? null) !== null ? number_format($deepKpis['revenue']['vertragstreue']['averageDaysCurrent'], 1).'d' : '—' }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('CTR') }}</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">
                            {{ number_format($deepKpis['revenue']['leadQuality']['ctr']['current'] ?? 0, 2) }}%
                        </div>
                    </div>
                </div>

                <a href="{{ route('financialplatform.deep-kpis.index') }}"
                   class="mt-4 inline-flex rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500">
                    {{ __('Configure Deep KPIs') }}
                </a>
            </details>

            @foreach (['profit' => 'Profit', 'order' => 'Order', 'influence' => 'Influence', 'legacy' => 'Legacy'] as $phase => $label)
                <details class="rounded-lg border border-slate-200 bg-white p-3">
                    <summary class="cursor-pointer list-none text-sm font-semibold text-slate-800">
                        {{ __($label) }}
                    </summary>
                    <p class="mt-3 text-sm text-slate-500">{{ $deepKpis[$phase]['note'] ?? __('Concrete KPIs for this pillar are not defined in the Deep KPI sheet yet.') }}</p>
                    <a href="{{ url('app/audit') }}" class="mt-2 inline-flex text-sm font-medium text-indigo-600 hover:underline">{{ __('Open AuditPro') }}</a>
                </details>
            @endforeach
        </div>
    </details>
</div>
