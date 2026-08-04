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

@include('partials.module-nav', ['layout' => \$layout ?? 'horizontal'])
