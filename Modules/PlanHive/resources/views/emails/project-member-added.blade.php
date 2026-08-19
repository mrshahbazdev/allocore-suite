@extends('emails.layout')

@section('title', __('You have been added to :project', ['project' => $projectName]))

@section('content')
    <p style="font-size:16px;line-height:1.6;color:#334155;margin:0 0 16px 0;">
        {{ __('Hello,') }}
    </p>
    <p style="font-size:15px;line-height:1.6;color:#475569;margin:0 0 24px 0;">
        {{ __('You have been added to the project :project with the role :role.', ['project' => $projectName, 'role' => $role]) }}
    </p>

    <p style="margin:24px 0;text-align:center;">
        <a href="{{ $projectUrl }}" style="display:inline-block;background-color:#ff9200;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:12px 28px;border-radius:8px;">
            {{ __('Open Project') }}
        </a>
    </p>
@endsection
