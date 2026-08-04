@php($links = [
    ['route' => 'cashcore.dashboard', 'label' => __('Dashboard')],
    ['route' => 'cashcore.transactions.index', 'label' => __('Transactions')],
    ['route' => 'cashcore.categories.index', 'label' => __('Categories')],
    ['route' => 'cashcore.leaks.index', 'label' => __('Leaks')],
    ['route' => 'cashcore.unlocker.index', 'label' => __('Unlocker')],
    ['route' => 'cashcore.scoring.index', 'label' => __('Scoring')],
    ['route' => 'cashcore.scenarios.index', 'label' => __('Scenarios')],
    ['route' => 'cashcore.allocation.index', 'label' => __('Allocation')],
    ['route' => 'cashcore.behavior.index', 'label' => __('Behavior')],
])

@include('partials.module-nav')
