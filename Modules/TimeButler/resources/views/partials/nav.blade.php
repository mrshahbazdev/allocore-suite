@php($links = [
    ['route' => 'timebutler.dashboard', 'active' => 'timebutler.dashboard', 'label' => __('Dashboard')],
    ['route' => 'timebutler.calendar.index', 'active' => 'timebutler.calendar.*', 'label' => __('Calendar')],
    ['route' => 'timebutler.absences.index', 'active' => 'timebutler.absences.*', 'label' => __('Absences')],
    ['route' => 'timebutler.absence-types.index', 'active' => 'timebutler.absence-types.*', 'label' => __('Absence types')],
    ['route' => 'timebutler.holidays.index', 'active' => 'timebutler.holidays.*', 'label' => __('Holidays')],
    ['route' => 'timebutler.time-tracking.index', 'active' => 'timebutler.time-tracking.*', 'label' => __('Time tracking')],
    ['route' => 'timebutler.reports.absences', 'active' => 'timebutler.reports.*', 'label' => __('Reports')],
])

@include('partials.module-nav', ['layout' => $layout ?? 'horizontal'])
