@extends('emails.layout')

@section('title', __('You are invited to join :team', ['team' => $teamName]))

@section('content')
    <p style="font-size:16px;line-height:1.6;color:#334155;margin:0 0 16px 0;">
        {{ __('Hello,') }}
    </p>
    <p style="font-size:15px;line-height:1.6;color:#475569;margin:0 0 24px 0;">
        @if ($projectName ?? null)
            {{ __(':name has invited you to join the team :team on project :project.', ['name' => $inviterName, 'team' => $teamName, 'project' => $projectName]) }}
        @else
            {{ __(':name has invited you to join the team :team.', ['name' => $inviterName, 'team' => $teamName]) }}
        @endif
    </p>

    <p style="margin:24px 0;text-align:center;">
        <a href="{{ $acceptUrl }}" style="display:inline-block;background-color:#ff9200;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:12px 28px;border-radius:8px;">
            {{ __('Accept Invitation') }}
        </a>
    </p>

    <p style="font-size:13px;line-height:1.5;color:#94a3b8;margin:24px 0 0 0;">
        {{ __('This invitation will expire in 7 days.') }}
    </p>
@endsection
