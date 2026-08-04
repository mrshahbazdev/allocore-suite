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

<div class="mb-6 border-b border-slate-200">
    <nav class="flex flex-wrap gap-6">
            @foreach ($links as $link)
                @php($active = request()->routeIs($link['active']))
                <a href="{{ route($link['route']) }}" class="-mb-px border-b-2 pb-3 text-sm font-medium transition {{ $active ? 'border-[#ff9200] text-[#ff9200]' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}">{{ $link['label'] }}
                </a>
            @endforeach
        </nav>
</div>
