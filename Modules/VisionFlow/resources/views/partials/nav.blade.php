@php($organization = request()->route('organization'))

@php($links = [
    ['route' => 'visionflow.dashboard', 'label' => __('Dashboard'), 'params' => []],
    ['route' => 'visionflow.organizations.index', 'label' => __('Organizations'), 'params' => []],
])

@if ($organization instanceof \Modules\VisionFlow\Models\Organization)
    @php($links = array_merge($links, [
        ['route' => 'visionflow.organizations.show', 'label' => __('Overview'), 'params' => [$organization]],
        ['route' => 'visionflow.organizations.values.index', 'label' => __('Values'), 'params' => [$organization]],
        ['route' => 'visionflow.organizations.principles.index', 'label' => __('Principles'), 'params' => [$organization]],
        ['route' => 'visionflow.organizations.strategic-goals.index', 'label' => __('Goals'), 'params' => [$organization]],
        ['route' => 'visionflow.organizations.visions.index', 'label' => __('Visions'), 'params' => [$organization]],
        ['route' => 'visionflow.organizations.missions.index', 'label' => __('Missions'), 'params' => [$organization]],
        ['route' => 'visionflow.organizations.projects.index', 'label' => __('Projects'), 'params' => [$organization]],
        ['route' => 'visionflow.organizations.decision-logs.index', 'label' => __('Decisions'), 'params' => [$organization]],
    ]))
@endif

@include('partials.module-nav', ['layout' => \$layout ?? 'horizontal'])
