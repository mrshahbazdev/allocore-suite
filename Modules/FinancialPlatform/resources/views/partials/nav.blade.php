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

<div class="mb-6 border-b border-slate-200">
    <nav class="flex flex-wrap gap-6">
            @foreach ($links as $link)
                @php($active = request()->routeIs($link['active']))
                <a href="{{ route($link['route']) }}" class="-mb-px border-b-2 pb-3 text-sm font-medium transition {{ $active ? 'border-[#ff9200] text-[#ff9200]' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}">{{ $link['label'] }}
                </a>
            @endforeach
        </nav>
</div>
