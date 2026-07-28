@extends('layouts.shell')

@section('title', $process->localizedName())
@section('page-title', $process->localizedName())

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $process->localizedName() }}</h1>
                <p class="text-sm text-slate-500">{{ $process->localizedDescription() }}</p>
                <div class="mt-2 text-xs text-slate-500">{{ $process->category ?? __('No category') }} — {{ __($process->status) }}</div>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('loopengine.runs.start', $process) }}" class="inline flex flex-wrap items-center gap-2">
                    @csrf
                    @if ($users->isNotEmpty())
                        <select name="assigned_to" class="rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">{{ __('Assign to me') }}</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Start Run') }}</button>
                </form>
                <a href="{{ route('loopengine.processes.edit', $process) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Edit') }}</a>
                <a href="{{ route('loopengine.processes.share', $process) }}" class="rounded-lg bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-200">{{ __('Share') }}</a>
                @if ($process->status !== 'active')
                    <form method="POST" action="{{ route('loopengine.processes.activate', $process) }}" class="inline">@csrf<button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Activate') }}</button></form>
                @endif
                @if ($process->status !== 'archived')
                    <form method="POST" action="{{ route('loopengine.processes.archive', $process) }}" class="inline">@csrf<button class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500">{{ __('Archive') }}</button></form>
                @endif
                <form method="POST" action="{{ route('loopengine.processes.version', $process) }}" class="inline">@csrf<button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('New Version') }}</button></form>
                <form method="POST" action="{{ route('loopengine.processes.duplicate', $process) }}" class="inline">@csrf<button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Copy') }}</button></form>
                <form method="POST" action="{{ route('loopengine.processes.destroy', $process) }}" class="inline" onsubmit="return confirm('{{ __('Delete this process?') }}')">@csrf @method('DELETE')<button class="rounded-lg bg-rose-100 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-200">{{ __('Delete') }}</button></form>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Steps') }}</h2>
            <ol class="mt-4 list-decimal space-y-3 pl-5">
                @foreach ($process->steps as $step)
                    <li>
                        <div class="font-medium">{{ $step->localizedQuestion() }}</div>
                        <div class="text-xs text-slate-500">{{ $step->step_type }} {{ $step->is_loop_checkpoint ? '— '.__('Loop checkpoint') : '' }}</div>
                        <ul class="mt-1 ml-4 list-disc text-sm text-slate-600">
                            @foreach ($step->options as $option)
                                <li>{{ $option->localizedLabel() }} ({{ $option->value }}) {{ $option->transition ? '→ '.__($option->transition->action_type) : '' }}</li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
@endsection
