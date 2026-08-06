@php($links = [
    ['route' => 'knowledgemanager.dashboard', 'active' => 'knowledgemanager.dashboard', 'label' => __('Dashboard')],
    ['route' => 'knowledgemanager.projects.index', 'active' => 'knowledgemanager.projects.*', 'label' => __('Projects')],
])
@include('partials.module-nav', ['layout' => $layout ?? 'horizontal'])
