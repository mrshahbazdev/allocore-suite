@component('mail::message')
# {{ __('New delegation assigned to you') }}

**{{ $delegation->task?->title }}**

{{ $delegation->goal }}

@component('mail::button', ['url' => route('focusmatrix.delegations.assigned')])
{{ __('View in FocusMatrix') }}
@endcomponent

{{ __('Thanks') }},<br>{{ config('app.name') }}
@endcomponent
