@php($links = [
    ['route' => 'customersuccess.dashboard', 'active' => 'customersuccess.dashboard', 'label' => __('Dashboard')],
    ['route' => 'customersuccess.inquiries.index', 'active' => 'customersuccess.inquiries.*', 'label' => __('Inquiries')],
    ['route' => 'customersuccess.inquiries.create', 'active' => 'customersuccess.inquiries.create', 'label' => __('Ask')],
])
@include('partials.module-nav', ['layout' => $layout ?? 'horizontal'])
