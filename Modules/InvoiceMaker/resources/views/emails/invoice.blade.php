@component('mail::message')
# {{ $subjectLine }}

@if ($message)
{{ $message }}
@endif

@component('mail::panel')
**{{ __('Invoice number') }}:** {{ $invoice->invoice_number }}<br>
**{{ __('Amount due') }}:** {{ $invoice->currency_symbol }}{{ number_format($invoice->amount_due, 2) }}<br>
**{{ __('Due date') }}:** {{ $invoice->due_date?->format('d M Y') }}
@endcomponent

@component('mail::button', ['url' => $url])
{{ __('View invoice') }}
@endcomponent

{{ __('Download PDF') }}: [{{ $downloadUrl }}]({{ $downloadUrl }})

{{ __('If you have any questions, reply to this email.') }}

{{ config('app.name') }}
@endcomponent
