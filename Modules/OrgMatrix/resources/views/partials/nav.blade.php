@php($organization = request()->route('organization'))

@php($links = [
    ['route' => 'orgmatrix.dashboard', 'label' => __('Dashboard'), 'params' => []],
    ['route' => 'orgmatrix.organizations.index', 'label' => __('Organizations'), 'params' => []],
])

@if ($organization instanceof \Modules\OrgMatrix\Models\Organization)
    @php($links = array_merge($links, [
        ['route' => 'orgmatrix.organizations.show', 'label' => __('Overview'), 'params' => [$organization]],
        ['route' => 'orgmatrix.organizations.roles.index', 'label' => __('Roles'), 'params' => [$organization]],
        ['route' => 'orgmatrix.organizations.people.index', 'label' => __('People'), 'params' => [$organization]],
        ['route' => 'orgmatrix.organizations.chart', 'label' => __('Org Chart'), 'params' => [$organization]],
    ]))
@endif

<div class="border-b border-slate-200 bg-white">
    <div class="max-w-full overflow-x-auto">
        <nav class="flex items-center gap-1 whitespace-nowrap px-4 py-2 sm:px-6 lg:px-8">
            @foreach ($links as $link)
                @php($routeName = $link['route'])
                @php($active = request()->routeIs($routeName) || request()->routeIs($routeName . '.*'))
                <a href="{{ route($routeName, $link['params']) }}" class="rounded-lg px-3 py-2 text-sm font-medium transition {{ $active ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</div>
