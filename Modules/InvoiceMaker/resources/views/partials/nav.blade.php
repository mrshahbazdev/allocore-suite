<nav class="mb-6 flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-white p-2 shadow-sm">
    @foreach ([
        'invoicemaker.dashboard' => __('Overview'),
        'invoicemaker.invoices.index' => __('Invoices'),
        'invoicemaker.estimates.index' => __('Estimates'),
        'invoicemaker.clients.index' => __('Clients'),
        'invoicemaker.products.index' => __('Products'),
        'invoicemaker.expenses.index' => __('Expenses'),
        'invoicemaker.cash-book.index' => __('Cash book'),
        'invoicemaker.templates.index' => __('Templates'),
        'invoicemaker.settings.profile' => __('Settings'),
    ] as $route => $label)
        <a href="{{ route($route) }}"
           class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs($route) ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            {{ $label }}
        </a>
    @endforeach
</nav>
