@extends('layouts.shell')

@section('title', $process->localizedName())
@section('page-title', $process->localizedName())

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $process->localizedName() }}</h1>
                <div class="text-sm text-slate-500">{{ $process->localizedDescription() }}</div>
            </div>
            <div class="flex gap-2 flex-wrap">
                <form method="POST" action="{{ route('loopengine.processes.activate', $process) }}" class="inline">@csrf<button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Activate') }}</button></form>
                <form method="POST" action="{{ route('loopengine.processes.archive', $process) }}" class="inline">@csrf<button class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500">{{ __('Archive') }}</button></form>
                <form method="POST" action="{{ route('loopengine.processes.version', $process) }}" class="inline">@csrf<button class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">{{ __('New Version') }}</button></form>
                <a href="{{ route('loopengine.processes.edit', $process) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Edit Details') }}</a>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Add Step') }}</h2>
            <form method="POST" action="{{ route('loopengine.steps.store', $process) }}" class="mt-4 space-y-3">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('Question (EN)') }}</label><textarea name="question_en" rows="2" class="mt-1 w-full rounded-lg border-slate-300" required></textarea></div>
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('Question (DE)') }}</label><textarea name="question_de" rows="2" class="mt-1 w-full rounded-lg border-slate-300"></textarea></div>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Step Type') }}</label>
                        <select name="step_type" class="mt-1 w-full rounded-lg border-slate-300">
                            <option value="question">{{ __('Question') }}</option>
                            <option value="decision">{{ __('Decision') }}</option>
                            <option value="loop_check">{{ __('Loop Check') }}</option>
                            <option value="info">{{ __('Info') }}</option>
                            <option value="end">{{ __('End') }}</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 pt-6"><input type="checkbox" name="is_loop_checkpoint" value="1" class="rounded border-slate-300"><span class="text-sm text-slate-700">{{ __('Loop checkpoint') }}</span></div>
                    <div class="flex items-center gap-2 pt-6"><input type="checkbox" name="is_required" value="1" checked class="rounded border-slate-300"><span class="text-sm text-slate-700">{{ __('Required') }}</span></div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('Help (EN)') }}</label><input type="text" name="help_text_en" class="mt-1 w-full rounded-lg border-slate-300"></div>
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('Help (DE)') }}</label><input type="text" name="help_text_de" class="mt-1 w-full rounded-lg border-slate-300"></div>
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('Max Loops') }}</label><input type="number" name="max_loops" min="0" value="0" class="mt-1 w-full rounded-lg border-slate-300"></div>
                </div>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add Step') }}</button>
            </form>
        </div>

        <div class="space-y-3">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Steps') }}</h2>
            @foreach ($process->steps as $step)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-semibold">{{ $step->order }}. {{ $step->localizedQuestion() }}</div>
                            <div class="text-xs text-slate-500">{{ $step->step_type }} {{ $step->is_loop_checkpoint ? '— '.__('Loop checkpoint') : '' }}</div>
                        </div>
                        <div class="flex gap-2 text-sm">
                            <a href="{{ route('loopengine.steps.edit', $step) }}" class="text-indigo-600">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('loopengine.steps.destroy', $step) }}" class="inline">@csrf @method('DELETE')<button class="text-rose-600">{{ __('Delete') }}</button></form>
                        </div>
                    </div>
                    <div class="mt-3 text-sm text-slate-600">
                        <p>{{ __('Options:') }} {{ $step->options->map(fn($o) => $o->localizedLabel())->implode(', ') ?: '-' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
