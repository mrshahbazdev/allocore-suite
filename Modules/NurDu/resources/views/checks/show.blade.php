@extends('layouts.shell', ['title' => __('Vision Check')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">{{ $check->check_date->format('Y-m-d') }}</h1>
        <div class="flex gap-3">
            <a href="{{ route('nurdu.checks.index') }}" class="text-indigo-600 hover:underline">{{ __('Back') }}</a>
            <form method="POST" action="{{ route('nurdu.checks.destroy', $check) }}" onsubmit="return confirm('{{ __('Delete?') }}')">
                @csrf @method('DELETE')
                <button class="text-rose-600 hover:underline">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <div>
            <div class="text-sm text-slate-500">{{ __('Does what we are doing now clearly pay into our vision?') }}</div>
            <div class="mt-1 inline-flex rounded-full px-2 py-1 text-sm font-medium bg-slate-100">{{ $check->q1_answer }}</div>
        </div>
        <div>
            <div class="text-sm text-slate-500">{{ __('What decision or activity is currently most moving us away from the vision?') }}</div>
            <p class="mt-1 text-slate-900">{{ $check->q2_answer }}</p>
        </div>
        <div>
            <div class="text-sm text-slate-500">{{ __('What is the one thing we need to change in the next period to get closer to the vision?') }}</div>
            <p class="mt-1 text-slate-900">{{ $check->q3_answer }}</p>
        </div>
        <div>
            <div class="text-sm text-slate-500">{{ __('Notes') }}</div>
            <p class="mt-1 text-slate-900">{{ $check->notes }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('Action Items') }}</h2>
        @if ($check->actionItems->isEmpty())
            <p class="text-slate-500">{{ __('No action items.') }}</p>
        @else
            <ul class="mt-4 space-y-2">
                @foreach ($check->actionItems as $item)
                    <li class="flex items-center justify-between text-sm">
                        <span class="{{ $item->completed ? 'line-through text-slate-400' : 'text-slate-700' }}">{{ $item->title }}</span>
                        <form method="POST" action="{{ route('nurdu.checks.action-items.toggle', $item) }}">
                            @csrf @method('PATCH')
                            <button class="text-indigo-600 hover:underline">{{ $item->completed ? __('Undo') : __('Complete') }}</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
