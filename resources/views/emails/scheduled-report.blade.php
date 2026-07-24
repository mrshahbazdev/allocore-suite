@component('mail::message')
# {{ __('Scheduled report') }}: {{ $scheduledReport->title }}

{{ __('Your scheduled report is attached.') }}

- **{{ __('Report type') }}:** {{ $scheduledReport->report_type }}
- **{{ __('Frequency') }}:** {{ $scheduledReport->frequency }}
- **{{ __('Format') }}:** {{ strtoupper($scheduledReport->format) }}

{{ __('Thanks') }},<br>
{{ config('app.name') }}
@endcomponent
