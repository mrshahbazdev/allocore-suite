@php($links = [
    [
        'route' => 'bookintelligence.dashboard',
        'label' => __('Dashboard'),
        'active' => 'bookintelligence.dashboard',
    ],
    [
        'route' => 'bookintelligence.books.index',
        'label' => __('Book Library'),
        'active' => 'bookintelligence.books.*',
    ],
    [
        'route' => 'bookintelligence.setup.index',
        'label' => __('Library Setup'),
        'active' => 'bookintelligence.setup.*',
    ],
    [
        'route' => 'bookintelligence.guide',
        'label' => __('How It Works'),
        'active' => 'bookintelligence.guide',
    ],
])

@include('partials.module-nav', ['layout' => $layout ?? 'horizontal'])
