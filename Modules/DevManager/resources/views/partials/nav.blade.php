@php($links = [
    ['route' => 'devmanager.dashboard', 'active' => 'devmanager.dashboard', 'label' => __('Dashboard')],
    ['route' => 'devmanager.ideas.index', 'active' => 'devmanager.ideas.*', 'label' => __('Ideas')],
    ['route' => 'devmanager.backlog.index', 'active' => 'devmanager.backlog.*', 'label' => __('Backlog')],
    ['route' => 'devmanager.roadmap.index', 'active' => 'devmanager.roadmap.*', 'label' => __('Roadmap')],
    ['route' => 'devmanager.integrations.index', 'active' => 'devmanager.integrations.*', 'label' => __('Integrations')],
])

@include('partials.module-nav', ['layout' => $layout ?? 'horizontal'])
