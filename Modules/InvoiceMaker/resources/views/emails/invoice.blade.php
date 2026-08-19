@extends('emails.layout')

@section('title', $subjectLine)

@section('content')
    @if ($bodyMessage)
        <p style="font-size:15px;line-height:1.6;color:#475569;margin:0 0 24px 0;">{{ $bodyMessage }}</p>
    @endif

    <table style="width:100%;border-collapse:collapse;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin:0 0 24px 0;">
        <tr>
            <td style="padding:16px;font-size:14px;color:#475569;">
                <p style="margin:0 0 8px 0;"><strong style="color:#0f172a;">{{ __('Invoice number') }}:</strong> {{ $invoiceNumber }}</p>
                <p style="margin:0 0 8px 0;"><strong style="color:#0f172a;">{{ __('Amount due') }}:</strong> {{ $currencySymbol }}{{ number_format($amountDue, 2) }}</p>
                <p style="margin:0;"><strong style="color:#0f172a;">{{ __('Due date') }}:</strong> {{ $dueDate }}</p>
            </td>
        </tr>
    </table>

    <p style="margin:24px 0;text-align:center;">
        <a href="{{ $url }}" style="display:inline-block;background-color:#ff9200;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:12px 28px;border-radius:8px;">
            {{ __('View invoice') }}
        </a>
    </p>

    <p style="font-size:14px;line-height:1.6;color:#475569;margin:24px 0 0 0;">
        {{ __('Download PDF') }}: <a href="{{ $downloadUrl }}" style="color:#0094af;text-decoration:none;">{{ $downloadUrl }}</a>
    </p>

    <p style="font-size:14px;line-height:1.6;color:#475569;margin:16px 0 0 0;">
        {{ __('If you have any questions, reply to this email.') }}
    </p>
@endsection
