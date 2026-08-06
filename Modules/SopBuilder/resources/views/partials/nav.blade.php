@php($links = [
    ['route' => 'sopbuilder.dashboard', 'active' => 'sopbuilder.dashboard', 'label' => __('Dashboard')],
    ['route' => 'sopbuilder.sops.index', 'active' => 'sopbuilder.sops.*', 'label' => __('SOPs')],
    ['route' => 'sopbuilder.categories.index', 'active' => 'sopbuilder.categories.*', 'label' => __('Categories')],
])

@include('partials.module-nav', ['layout' => $layout ?? 'horizontal'])
