@php($links = [
    ['route' => 'sweetspot.dashboard', 'active' => 'sweetspot.dashboard', 'label' => __('Dashboard')],
    ['route' => 'sweetspot.customers.index', 'active' => 'sweetspot.customers.*', 'label' => __('Customers')],
    ['route' => 'sweetspot.settings.index', 'active' => 'sweetspot.settings.*', 'label' => __('Settings')],
])

@include('partials.module-nav', ['layout' => \$layout ?? 'horizontal'])
