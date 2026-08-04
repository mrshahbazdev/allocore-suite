@php($links = [
    ['route' => 'nurdu.dashboard', 'label' => __('Dashboard')],
    ['route' => 'nurdu.vision.index', 'label' => __('Vision')],
    ['route' => 'nurdu.quarterly.index', 'label' => __('Quarterly')],
    ['route' => 'nurdu.decisions.index', 'label' => __('Decisions')],
    ['route' => 'nurdu.checks.index', 'label' => __('Checks')],
])

@include('partials.module-nav', ['layout' => \$layout ?? 'horizontal'])
