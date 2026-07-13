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

    @php($revenueDevelopment = $revenueDevelopment ?? app(\Modules\FinancialPlatform\Services\RevenueDevelopmentSnapshot::class)->forTeam(auth()->user()?->currentTeam))

    <details class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
        <summary class="cursor-pointer list-none text-sm font-semibold text-slate-900">
            {{ __('Business Readiness') }}
        </summary>

        <div class="mt-4 space-y-3">
            <details class="rounded-lg border border-slate-200 bg-white p-3" open>
                <summary class="cursor-pointer list-none text-sm font-semibold text-slate-800">
                    {{ __('Revenue Development') }}
                </summary>

                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Target sales') }}</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">
                            {{ number_format($revenueDevelopment['targetSales'] ?? 0, 2) }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Actual sales') }}</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">
                            {{ number_format($revenueDevelopment['actualSales'] ?? 0, 2) }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Achievement') }}</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">
                            {{ number_format($revenueDevelopment['percentage'] ?? 0, 1) }}%
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-xs text-slate-500">
                    {{ __('Source') }}: {{ $revenueDevelopment['sourceLabel'] ?? __('InvoiceMaker') }}
                    · {{ __('Status') }}:
                    <span class="font-medium text-slate-700">{{ ucfirst($revenueDevelopment['status'] ?? 'neutral') }}</span>
                </div>

                <a href="{{ route('financialplatform.revenue-development.edit') }}"
                   class="mt-4 inline-flex rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500">
                    {{ __('Configure target') }}
                </a>
            </details>

            <details class="rounded-lg border border-slate-200 bg-white p-3">
                <summary class="cursor-pointer list-none text-sm font-semibold text-slate-800">
                    {{ __('Profit') }}
                </summary>
                <p class="mt-3 text-sm text-slate-500">{{ __('Phase 1 phase-specific sub-KPIs will appear here.') }}</p>
            </details>

            <details class="rounded-lg border border-slate-200 bg-white p-3">
                <summary class="cursor-pointer list-none text-sm font-semibold text-slate-800">
                    {{ __('Order') }}
                </summary>
                <p class="mt-3 text-sm text-slate-500">{{ __('Phase 1 phase-specific sub-KPIs will appear here.') }}</p>
            </details>

            <details class="rounded-lg border border-slate-200 bg-white p-3">
                <summary class="cursor-pointer list-none text-sm font-semibold text-slate-800">
                    {{ __('Influence') }}
                </summary>
                <p class="mt-3 text-sm text-slate-500">{{ __('Phase 1 phase-specific sub-KPIs will appear here.') }}</p>
            </details>

            <details class="rounded-lg border border-slate-200 bg-white p-3">
                <summary class="cursor-pointer list-none text-sm font-semibold text-slate-800">
                    {{ __('Legacy') }}
                </summary>
                <p class="mt-3 text-sm text-slate-500">{{ __('Phase 1 phase-specific sub-KPIs will appear here.') }}</p>
            </details>
        </div>
    </details>
</div>
