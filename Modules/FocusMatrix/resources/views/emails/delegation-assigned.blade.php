@extends('emails.layout')

@section('title', __('New delegation assigned to you'))

@section('content')
    <p style="font-size:16px;line-height:1.6;color:#334155;margin:0 0 16px 0;">
        {{ __('Hello,') }}
    </p>

    @if ($taskTitle)
        <p style="font-size:15px;line-height:1.6;color:#475569;margin:0 0 8px 0;">
            <strong style="color:#0f172a;">{{ $taskTitle }}</strong>
        </p>
    @endif

    <p style="font-size:15px;line-height:1.6;color:#475569;margin:0 0 24px 0;">
        {{ $goal }}
    </p>

    <p style="margin:24px 0;text-align:center;">
        <a href="{{ $url }}" style="display:inline-block;background-color:#ff9200;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:12px 28px;border-radius:8px;">
            {{ $buttonText }}
        </a>
    </p>
@endsection
