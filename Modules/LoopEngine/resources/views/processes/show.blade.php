@extends('layouts.shell')

@section('title', $process->localizedName())
@section('page-title', $process->localizedName())

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $process->localizedName() }}</h1>
                <p class="text-sm text-slate-500">{{ $process->localizedDescription() }}</p>
                <div class="mt-2 text-xs text-slate-500">{{ $process->category }} — {{ __($process->status) }}</div>
            </div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('loopengine.runs.start', $process) }}" class="inline">@csrf<button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Start Run') }}</button></form>
                <a href="{{ route('loopengine.processes.edit', $process) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('Edit') }}</a>
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
