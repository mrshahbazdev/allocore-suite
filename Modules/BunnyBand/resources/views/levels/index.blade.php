@extends('layouts.shell')

@section('title', __('Levels'))

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Levels') }}</h1>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($levels as $level)
                <div class="rounded-2xl border {{ $profile->level_id === $level->id ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-slate-200' }} bg-white p-5 shadow-sm">
                    <div class="text-lg font-bold">{{ $level->name }}</div>
                    <div class="text-sm text-slate-500">{{ $level->type }} {{ $level->price > 0 ? '— '.number_format($level->price, 2) : '' }}</div>
                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                        <li>{{ __('Daily limit') }}: {{ $level->daily_earning_limit }}</li>
                        <li>{{ __('Task bonus') }}: {{ $level->task_bonus_percent }}%</li>
                        <li>{{ __('Withdrawal limit') }}: {{ $level->withdrawal_limit }}</li>
                    </ul>
                    @if ($profile->level_id !== $level->id)
                        <form method="POST" action="{{ route('bunnyband.levels.upgrade', $level) }}" class="mt-3">@csrf<button class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Upgrade') }}</button></form>
                    @else
                        <div class="mt-3 text-sm font-semibold text-indigo-600">{{ __('Current') }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endsection
