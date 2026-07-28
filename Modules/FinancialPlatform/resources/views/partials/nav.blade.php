@php($links = [
    ['route' => 'financial.dashboard', 'active' => 'financial.dashboard', 'label' => __('Dashboard')],
    ['route' => 'companies.index', 'active' => 'companies.*', 'label' => __('Companies')],
    ['route' => 'gmbh.index', 'active' => 'gmbh.*', 'label' => __('GmbH')],
    ['route' => 'jahresabschluss.index', 'active' => 'jahresabschluss.*', 'label' => __('Annual')],
    ['route' => 'immobilien.index', 'active' => 'immobilien.*', 'label' => __('Real Estate')],
    ['route' => 'analyses.index', 'active' => 'analyses.*', 'label' => __('Analyses')],
    ['route' => 'leads.index', 'active' => 'leads.*', 'label' => __('Leads')],
    ['route' => 'paypal.index', 'active' => 'paypal.*', 'label' => __('PayPal')],
    ['route' => 'bank-transactions.index', 'active' => 'bank-transactions.*', 'label' => __('Bank')],
    ['route' => 'budgets.index', 'active' => 'budgets.*', 'label' => __('Budgets')],
    ['route' => 'exchange-rates.index', 'active' => 'exchange-rates.*', 'label' => __('FX')],
    ['route' => 'kpi-schedules.index', 'active' => 'kpi-schedules.*', 'label' => __('Schedules')],
    ['route' => 'revenue-development.edit', 'active' => 'revenue-development.*', 'label' => __('Revenue')],
    ['route' => 'deep-kpis.index', 'active' => 'deep-kpis.*', 'label' => __('Deep KPIs')],
    ['route' => 'import.index', 'active' => 'import.*', 'label' => __('Import')],
])

<div class="border-b border-slate-200 bg-white">
    <div class="max-w-full overflow-x-auto">
        <nav class="flex items-center gap-1 whitespace-nowrap px-4 py-2 sm:px-6 lg:px-8">
            @foreach ($links as $link)
                @php($active = request()->routeIs($link['active']))
                <a href="{{ route($link['route']) }}" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ $active ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</div>
