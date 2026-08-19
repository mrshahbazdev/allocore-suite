@extends('emails.layout')

@section('title', __('Scheduled report').': '.$title)

@section('content')
    <p style="font-size:15px;line-height:1.6;color:#475569;margin:0 0 24px 0;">
        {{ __('Your scheduled report is attached.') }}
    </p>

    <table style="width:100%;border-collapse:collapse;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin:0 0 24px 0;">
        <tr>
            <td style="padding:16px;font-size:14px;color:#475569;">
                <p style="margin:0 0 8px 0;"><strong style="color:#0f172a;">{{ __('Report type') }}:</strong> {{ $reportType }}</p>
                <p style="margin:0 0 8px 0;"><strong style="color:#0f172a;">{{ __('Frequency') }}:</strong> {{ $frequency }}</p>
                <p style="margin:0;"><strong style="color:#0f172a;">{{ __('Format') }}:</strong> {{ strtoupper($format) }}</p>
            </td>
        </tr>
    </table>
@endsection
