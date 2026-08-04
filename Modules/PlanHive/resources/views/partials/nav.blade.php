@php($links = [
    ['route' => 'planhive.dashboard', 'active' => 'planhive.dashboard', 'label' => __('Dashboard')],
    ['route' => 'planhive.projects.index', 'active' => 'planhive.projects.*', 'label' => __('Projects')],
    ['route' => 'planhive.calendar.index', 'active' => 'planhive.calendar.*', 'label' => __('Calendar')],
    ['route' => 'planhive.reminders.all', 'active' => 'planhive.reminders.*', 'label' => __('Reminders')],
    ['route' => 'planhive.reports.index', 'active' => 'planhive.reports.*', 'label' => __('Reports')],
    ['route' => 'planhive.search', 'active' => 'planhive.search', 'label' => __('Search')],
])

@include('partials.module-nav', ['layout' => \$layout ?? 'horizontal'])
