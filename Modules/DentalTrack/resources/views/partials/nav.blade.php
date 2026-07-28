@php($isAdmin = auth()->user()?->isAdmin())

@php($links = [
    ['route' => 'dentaltrack.dashboard', 'label' => __('Dashboard'), 'admin' => false],
    ['route' => 'dentaltrack.track', 'label' => __('Track Order'), 'admin' => false],
    ['route' => 'dentaltrack.scan.index', 'label' => __('Scan QR'), 'admin' => false],
])

@if ($isAdmin)
    @php($links = array_merge($links, [
        ['route' => 'dentaltrack.admin.dashboard', 'label' => __('Admin'), 'admin' => true],
        ['route' => 'dentaltrack.admin.orders.index', 'label' => __('Orders'), 'admin' => true],
        ['route' => 'dentaltrack.admin.workstations.index', 'label' => __('Workstations'), 'admin' => true],
        ['route' => 'dentaltrack.admin.labs.index', 'label' => __('Labs'), 'admin' => true],
        ['route' => 'dentaltrack.admin.companies.index', 'label' => __('Companies'), 'admin' => true],
        ['route' => 'dentaltrack.admin.product-types.index', 'label' => __('Product Types'), 'admin' => true],
        ['route' => 'dentaltrack.admin.process-templates.index', 'label' => __('Templates'), 'admin' => true],
        ['route' => 'dentaltrack.admin.scan-events.index', 'label' => __('Scan Events'), 'admin' => true],
        ['route' => 'dentaltrack.admin.rework-events.index', 'label' => __('Rework'), 'admin' => true],
        ['route' => 'dentaltrack.admin.reports.index', 'label' => __('Reports'), 'admin' => true],
        ['route' => 'dentaltrack.admin.predictions.index', 'label' => __('Predictions'), 'admin' => true],
        ['route' => 'dentaltrack.admin.quality.index', 'label' => __('Quality'), 'admin' => true],
        ['route' => 'dentaltrack.admin.station-monitoring.index', 'label' => __('Stations'), 'admin' => true],
        ['route' => 'dentaltrack.admin.employee-performance.index', 'label' => __('Performance'), 'admin' => true],
    ]))
@endif

<div class="border-b border-slate-200 bg-white">
    <div class="max-w-full overflow-x-auto">
        <nav class="flex items-center gap-1 whitespace-nowrap px-4 py-2 sm:px-6 lg:px-8">
            @foreach ($links as $link)
                @php($active = request()->routeIs($link['route']) || request()->routeIs($link['route'] . '.*') || (str_starts_with($link['route'], 'dentaltrack.admin.') && request()->routeIs($link['route'] . '*')))
                <a href="{{ route($link['route']) }}" class="rounded-lg px-3 py-2 text-sm font-medium transition {{ $active ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</div>
