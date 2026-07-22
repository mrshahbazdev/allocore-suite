@extends('layouts.shell')

@section('title', __('Tasks'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Tasks') }}</h1>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Available') }}</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($tasks as $task)
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="font-semibold">{{ $task->title }}</div>
                        <div class="text-sm text-slate-500">{{ $task->type }} — {{ $task->verification_method }}</div>
                        @if ($task->url)<a href="{{ $task->url }}" target="_blank" class="text-sm text-indigo-600">{{ __('Open link') }}</a>@endif
                        <div class="mt-2 text-lg font-bold text-indigo-600">+{{ number_format($task->reward, 2) }}</div>
                        <form method="POST" action="{{ route('bunnyband.tasks.complete', $task) }}" class="mt-3">@csrf<button class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Complete') }}</button></form>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $tasks->links() }}</div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('My Tasks') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($myTasks as $ut)
                    <li class="flex justify-between"><span>{{ $ut->task->title }}</span><span class="rounded-full px-2 py-0.5 text-xs {{ $ut->status === 'verified' ? 'bg-emerald-100 text-emerald-700' : ($ut->status === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">{{ $ut->status }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
