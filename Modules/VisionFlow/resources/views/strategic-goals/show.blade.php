@extends('layouts.shell', ['title' => $item->title ?? $item->name ?? $item->content ?? __('Strategic Goals')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ $item->title ?? $item->name ?? $item->content }}</h1>
        <a href="{{ route('visionflow.organizations.strategic-goals.index', $organization) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">{{ __('Back') }}</a>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <div><div class="text-sm text-slate-500">{{ __('Title') }}</div><div class="mt-1 text-slate-900">{{ $item->title }}</div></div><div><div class="text-sm text-slate-500">{{ __('Description') }}</div><div class="mt-1 text-slate-900">{{ $item->description }}</div></div><div><div class="text-sm text-slate-500">{{ __('Category') }}</div><div class="mt-1"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-slate-100 text-slate-700">{{ $item->category }}</span></div></div><div><div class="text-sm text-slate-500">{{ __('Time Horizon') }}</div><div class="mt-1 text-slate-900">{{ $item->time_horizon }}</div></div><div><div class="text-sm text-slate-500">{{ __('Status') }}</div><div class="mt-1"><span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-slate-100 text-slate-700">{{ $item->status }}</span></div></div>
    </div>
</div>
@endsection
