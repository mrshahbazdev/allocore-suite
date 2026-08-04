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

@include('partials.module-nav', ['layout' => \$layout ?? 'horizontal'])
