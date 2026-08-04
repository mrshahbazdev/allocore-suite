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

<div class="mb-6 border-b border-slate-200">
    <nav class="flex flex-wrap gap-6">
            @foreach ($links as $link)
                @php($routeName = $link['route'])
                @php($active = request()->routeIs($routeName) || request()->routeIs($routeName . '.*'))
                <a href="{{ route($routeName, $link['params'] ?? []) }}" class="-mb-px border-b-2 pb-3 text-sm font-medium transition {{ $active ? 'border-[#ff9200] text-[#ff9200]' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}">{{ $link['label'] }}
                </a>
            @endforeach
        </nav>
</div>
