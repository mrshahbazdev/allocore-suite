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

<div class="mb-6 border-b border-slate-200">
    <nav class="flex flex-wrap gap-6">
            @foreach ($links as $link)
                @php($routeName = $link['route'])
                @php($active = request()->routeIs($routeName) || request()->routeIs($routeName . '.*'))
                <a href="{{ route($routeName) }}" class="-mb-px border-b-2 pb-3 text-sm font-medium transition {{ $active ? 'border-[#ff9200] text-[#ff9200]' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}">{{ $link['label'] }}
                </a>
            @endforeach
        </nav>
</div>
