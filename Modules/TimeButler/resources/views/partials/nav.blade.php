@php($links = [
    ['route' => 'timebutler.dashboard', 'active' => 'timebutler.dashboard', 'label' => __('Dashboard')],
    ['route' => 'timebutler.calendar.index', 'active' => 'timebutler.calendar.*', 'label' => __('Calendar')],
    ['route' => 'timebutler.absences.index', 'active' => 'timebutler.absences.*', 'label' => __('Absences')],
    ['route' => 'timebutler.absence-types.index', 'active' => 'timebutler.absence-types.*', 'label' => __('Absence types')],
    ['route' => 'timebutler.holidays.index', 'active' => 'timebutler.holidays.*', 'label' => __('Holidays')],
    ['route' => 'timebutler.time-tracking.index', 'active' => 'timebutler.time-tracking.*', 'label' => __('Time tracking')],
    ['route' => 'timebutler.reports.absences', 'active' => 'timebutler.reports.*', 'label' => __('Reports')],
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
