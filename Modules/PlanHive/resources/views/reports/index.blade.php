@extends('layouts.shell')

@section('title', __('Reports'))
@section('page-title', __('Reports'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('PlanHive Reports') }}</h1>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Projects') }}</div><div class="text-2xl font-bold">{{ $projects }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Tasks') }}</div><div class="text-2xl font-bold">{{ $tasks }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Done') }}</div><div class="text-2xl font-bold">{{ $doneTasks }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Active Goals') }}</div><div class="text-2xl font-bold">{{ $activeGoals }}</div></div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Tasks by Status') }}</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($statusCounts as $status => $count)
                        <li class="flex justify-between"><span class="capitalize">{{ __($status) }}</span><span class="font-medium">{{ $count }}</span></li>
                    @endforeach
                </ul>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Tasks by Priority') }}</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($priorityCounts as $priority => $count)
                        <li class="flex justify-between"><span class="capitalize">{{ __($priority) }}</span><span class="font-medium">{{ $count }}</span></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection
