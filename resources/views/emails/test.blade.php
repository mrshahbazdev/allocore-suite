@extends('emails.layout')

@section('title', __('Test email'))

@section('content')
    <p style="font-size:16px;line-height:1.6;color:#334155;margin:0 0 16px 0;">
        {{ __('Hello :name', ['name' => $userName]) }},
    </p>
    <p style="font-size:15px;line-height:1.6;color:#475569;margin:0 0 24px 0;">
        {{ __('This is a test email from :app. Your SMTP settings are working correctly.', ['app' => $appName]) }}
    </p>
    <p style="font-size:13px;line-height:1.5;color:#94a3b8;margin:24px 0 0 0;">
        {{ __('If you did not request this test, you can ignore it.') }}
    </p>
@endsection
