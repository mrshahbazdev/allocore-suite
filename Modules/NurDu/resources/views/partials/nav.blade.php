@php($links = [
    ['route' => 'nurdu.dashboard', 'label' => __('Dashboard')],
    ['route' => 'nurdu.vision.index', 'label' => __('Vision')],
    ['route' => 'nurdu.quarterly.index', 'label' => __('Quarterly')],
    ['route' => 'nurdu.decisions.index', 'label' => __('Decisions')],
    ['route' => 'nurdu.checks.index', 'label' => __('Checks')],
])

<div class="mb-6 border-b border-slate-200">
    <nav class="flex flex-wrap gap-6">
            @foreach ($links as $link)
                @php($active = request()->routeIs($link['route']) || request()->routeIs($link['route'] . '.*') || request()->routeIs(str_replace('.index', '.*', $link['route'])))
                <a href="{{ route($link['route']) }}" class="-mb-px border-b-2 pb-3 text-sm font-medium transition {{ $active ? 'border-[#ff9200] text-[#ff9200]' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}">{{ $link['label'] }}
                </a>
            @endforeach
        </nav>
</div>
