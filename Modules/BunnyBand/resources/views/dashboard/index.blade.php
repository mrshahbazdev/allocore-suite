@extends('layouts.shell')

@section('title', __('BunnyBand'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('BunnyBand') }}</h1>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Balance') }}</div><div class="text-2xl font-bold">{{ number_format($profile->balance, 2) }}</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Task Earnings') }}</div><div class="text-2xl font-bold">{{ number_format($profile->task_earnings, 2) }}</div></div>
            <div class="rounded-2xl border border-slate-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase text-slate-500">{{ __('Level') }}</div><div class="text-2xl font-bold">{{ $profile->level?->name ?? '-' }}</div></div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Available Tasks') }}</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($tasks as $task)
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="font-semibold">{{ $task->title }}</div>
                        <div class="text-sm text-slate-500">{{ $task->verification_method }}</div>
                        <div class="mt-2 text-lg font-bold text-indigo-600">+{{ number_format($task->reward, 2) }}</div>
                        <form method="POST" action="{{ route('bunnyband.tasks.complete', $task) }}" class="mt-3">@csrf<button class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Complete') }}</button></form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
