@extends('emails.layout')

@section('title', __('KPI Report for :team', ['team' => $teamName]))

@section('content')
    <p style="font-size:15px;line-height:1.6;color:#475569;margin:0 0 24px 0;">
        {{ __('Period') }}: <strong style="color:#0f172a;">{{ $period }}</strong>
    </p>

    <table style="width:100%;border-collapse:collapse;margin:0 0 24px 0;">
        <thead>
            <tr>
                <th style="text-align:left;padding:10px 8px;border-bottom:2px solid #ff9200;color:#0f172a;font-size:14px;">{{ __('KPI') }}</th>
                <th style="text-align:left;padding:10px 8px;border-bottom:2px solid #ff9200;color:#0f172a;font-size:14px;">{{ __('Value') }}</th>
                <th style="text-align:left;padding:10px 8px;border-bottom:2px solid #ff9200;color:#0f172a;font-size:14px;">{{ __('Score') }}</th>
                <th style="text-align:left;padding:10px 8px;border-bottom:2px solid #ff9200;color:#0f172a;font-size:14px;">{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($summaryRows as $row)
                <tr>
                    <td style="padding:10px 8px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#475569;">{{ $row['name'] ?? '' }}</td>
                    <td style="padding:10px 8px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#475569;">{{ $row['value'] ?? '' }}</td>
                    <td style="padding:10px 8px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#475569;">{{ $row['score'] ?? '' }}</td>
                    <td style="padding:10px 8px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#475569;">{{ $row['status'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="font-size:14px;line-height:1.6;color:#475569;margin:0;">
        {{ __('View the full dashboard in the app.') }}
    </p>
@endsection
