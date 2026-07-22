@extends('layouts.shell', ['title' => $item->title ?? $item->name ?? $item->content ?? __('Decision Log')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ $item->title ?? $item->name ?? $item->content }}</h1>
        <a href="{{ route('visionflow.organizations.decision-logs.index', $organization) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Back') }}</a>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <div><div class="text-sm text-slate-500">{{ __('Title') }}</div><div class="mt-1 text-slate-900">{{ $item->title }}</div></div><div><div class="text-sm text-slate-500">{{ __('Description') }}</div><div class="mt-1 text-slate-900">{{ $item->description }}</div></div><div><div class="text-sm text-slate-500">{{ __('Decision') }}</div><div class="mt-1 text-slate-900">{{ $item->decision }}</div></div><div><div class="text-sm text-slate-500">{{ __('Supporting Value') }}</div><div class="mt-1 text-slate-900">{{ $item->value.title }}</div></div><div><div class="text-sm text-slate-500">{{ __('Supporting Mission') }}</div><div class="mt-1 text-slate-900">{{ $item->mission.title }}</div></div><div><div class="text-sm text-slate-500">{{ __('Recorded By') }}</div><div class="mt-1 text-slate-900">{{ $item->user.name }}</div></div>
    </div>
</div>
@endsection
