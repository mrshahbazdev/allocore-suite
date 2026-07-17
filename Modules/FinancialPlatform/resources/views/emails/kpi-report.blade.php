@component('mail::message')
# {{ __('KPI Report for :team', ['team' => $teamName]) }}

{{ __('Period') }}: {{ $period }}

@component('mail::table')
| {{ __('KPI') }} | {{ __('Value') }} | {{ __('Score') }} | {{ __('Status') }} |
|---|---|---|---|
@foreach ($summary as $row)
| {{ $row['name'] }} | {{ $row['value'] }} | {{ $row['score'] }} | {{ $row['status'] }} |
@endforeach
@endcomponent

{{ __('View the full dashboard in the app.') }}

{{ config('app.name') }}
@endcomponent
