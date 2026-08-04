@php($links = [
    ['route' => 'planhive.dashboard', 'active' => 'planhive.dashboard', 'label' => __('Dashboard')],
    ['route' => 'planhive.projects.index', 'active' => 'planhive.projects.*', 'label' => __('Projects')],
    ['route' => 'planhive.calendar.index', 'active' => 'planhive.calendar.*', 'label' => __('Calendar')],
    ['route' => 'planhive.reminders.all', 'active' => 'planhive.reminders.*', 'label' => __('Reminders')],
    ['route' => 'planhive.reports.index', 'active' => 'planhive.reports.*', 'label' => __('Reports')],
    ['route' => 'planhive.search', 'active' => 'planhive.search', 'label' => __('Search')],
])

<div class="mb-6 border-b border-slate-200">
    <nav class="flex flex-wrap gap-6">
            @foreach ($links as $link)
                @php($active = request()->routeIs($link['active']))
                <a href="{{ route($link['route']) }}" class="-mb-px border-b-2 pb-3 text-sm font-medium transition {{ $active ? 'border-[#ff9200] text-[#ff9200]' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}">{{ $link['label'] }}
                </a>
            @endforeach
        </nav>
</div>
