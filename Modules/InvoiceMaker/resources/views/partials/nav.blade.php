@php($links = [
    ['route' => 'invoicemaker.dashboard', 'active' => 'invoicemaker.dashboard', 'label' => __('Overview')],
    ['route' => 'invoicemaker.invoices.index', 'active' => 'invoicemaker.invoices.*', 'label' => __('Invoices')],
    ['route' => 'invoicemaker.estimates.index', 'active' => 'invoicemaker.estimates.*', 'label' => __('Estimates')],
    ['route' => 'invoicemaker.clients.index', 'active' => 'invoicemaker.clients.*', 'label' => __('Clients')],
    ['route' => 'invoicemaker.products.index', 'active' => 'invoicemaker.products.*', 'label' => __('Products')],
    ['route' => 'invoicemaker.expenses.index', 'active' => 'invoicemaker.expenses.*', 'label' => __('Expenses')],
    ['route' => 'invoicemaker.cash-book.index', 'active' => 'invoicemaker.cash-book.*', 'label' => __('Cash book')],
    ['route' => 'invoicemaker.templates.index', 'active' => 'invoicemaker.templates.*', 'label' => __('Templates')],
    ['route' => 'invoicemaker.settings.profile', 'active' => 'invoicemaker.settings.*', 'label' => __('Settings')],
])

@include('partials.module-nav', ['layout' => \$layout ?? 'horizontal'])
