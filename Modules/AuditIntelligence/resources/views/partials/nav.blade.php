@php($links = [
    ['route' => 'auditintelligence.dashboard', 'active' => 'auditintelligence.dashboard', 'label' => __('Dashboard')],
    ['route' => 'auditintelligence.findings.index', 'active' => 'auditintelligence.findings.*', 'label' => __('Findings')],
])
@include('partials.module-nav', ['layout' => $layout ?? 'horizontal'])
