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

<div class="border-b border-slate-200 bg-white">
    <div class="max-w-full overflow-x-auto">
        <nav class="flex items-center gap-1 whitespace-nowrap px-4 py-2 sm:px-6 lg:px-8">
            @foreach ($links as $link)
                @php($routeName = $link['route'])
                @php($active = request()->routeIs($routeName) || request()->routeIs($routeName . '.*'))
                <a href="{{ route($routeName, $link['params'] ?? []) }}" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ $active ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</div>
