@extends('layouts.shell')

@section('title', __('Edit Step'))
@section('page-title', __('Edit Step'))

@section('content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Edit Step') }}</h1>
            <a href="{{ route('loopengine.processes.edit', $step->process) }}" class="text-indigo-600">{{ __('Back') }}</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('loopengine.steps.update', $step) }}" class="space-y-4">
                @csrf @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('Question (EN)') }}</label><textarea name="question_en" rows="2" class="mt-1 w-full rounded-lg border-slate-300" required>{{ old('question_en', $step->question_en) }}</textarea></div>
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('Question (DE)') }}</label><textarea name="question_de" rows="2" class="mt-1 w-full rounded-lg border-slate-300">{{ old('question_de', $step->question_de) }}</textarea></div>
                </div>
                <div class="grid gap-4 sm:grid-cols-4">
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('Order') }}</label><input type="number" name="order" value="{{ old('order', $step->order) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Step Type') }}</label>
                        <select name="step_type" class="mt-1 w-full rounded-lg border-slate-300">
                            @foreach (['question' => 'Question', 'decision' => 'Decision', 'loop_check' => 'Loop Check', 'info' => 'Info', 'end' => 'End'] as $key => $label)
                                <option value="{{ $key }}" {{ old('step_type', $step->step_type) === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2 pt-6"><input type="checkbox" name="is_loop_checkpoint" value="1" {{ old('is_loop_checkpoint', $step->is_loop_checkpoint) ? 'checked' : '' }} class="rounded border-slate-300"><span class="text-sm text-slate-700">{{ __('Loop checkpoint') }}</span></div>
                    <div class="flex items-center gap-2 pt-6"><input type="checkbox" name="is_required" value="1" {{ old('is_required', $step->is_required) ? 'checked' : '' }} class="rounded border-slate-300"><span class="text-sm text-slate-700">{{ __('Required') }}</span></div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('Help (EN)') }}</label><input type="text" name="help_text_en" value="{{ old('help_text_en', $step->help_text_en) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('Help (DE)') }}</label><input type="text" name="help_text_de" value="{{ old('help_text_de', $step->help_text_de) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                    <div><label class="block text-sm font-medium text-slate-700">{{ __('Max Loops') }}</label><input type="number" name="max_loops" min="0" value="{{ old('max_loops', $step->max_loops) }}" class="mt-1 w-full rounded-lg border-slate-300"></div>
                </div>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Update Step') }}</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Add Option') }}</h2>
            <form method="POST" action="{{ route('loopengine.options.store', $step) }}" class="mt-4 grid gap-4 sm:grid-cols-5">
                @csrf
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Label (EN)') }}</label><input type="text" name="label_en" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Label (DE)') }}</label><input type="text" name="label_de" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Value') }}</label><input type="text" name="value" class="mt-1 w-full rounded-lg border-slate-300" required></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Color') }}</label><input type="text" name="color" class="mt-1 w-full rounded-lg border-slate-300" placeholder="green"></div>
                <div class="flex items-end"><button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add') }}</button></div>
            </form>

            <table class="mt-4 min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Label') }}</th><th class="pb-2 pr-4">{{ __('Value') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($step->options as $option)
                        <tr>
                            <td class="py-2 pr-4">{{ $option->localizedLabel() }}</td>
                            <td class="py-2 pr-4">{{ $option->value }}</td>
                            <td class="py-2"><form method="POST" action="{{ route('loopengine.options.destroy', $option) }}" class="inline">@csrf @method('DELETE')<button class="text-rose-600">{{ __('Delete') }}</button></form></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Add Transition') }}</h2>
            <form method="POST" action="{{ route('loopengine.transitions.store', $step) }}" class="mt-4 grid gap-4 sm:grid-cols-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Option') }}</label>
                    <select name="option_id" class="mt-1 w-full rounded-lg border-slate-300">
                        <option value="">{{ __('Default / None') }}</option>
                        @foreach ($step->options as $option)
                            <option value="{{ $option->id }}">{{ $option->localizedLabel() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Action') }}</label>
                    <select name="action_type" class="mt-1 w-full rounded-lg border-slate-300">
                        <option value="next_step">{{ __('Next Step') }}</option>
                        <option value="goto_step">{{ __('Go To Step') }}</option>
                        <option value="start_process">{{ __('Start Process') }}</option>
                        <option value="loop_back">{{ __('Loop Back') }}</option>
                        <option value="end">{{ __('End') }}</option>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Target Step ID') }}</label><input type="number" name="target_step_id" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="block text-sm font-medium text-slate-700">{{ __('Target Process ID') }}</label><input type="number" name="target_process_id" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div class="flex items-end"><button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add') }}</button></div>
            </form>

            <table class="mt-4 min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-2 pr-4">{{ __('Option') }}</th><th class="pb-2 pr-4">{{ __('Action') }}</th><th class="pb-2 pr-4">{{ __('Target') }}</th><th class="pb-2"></th></tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($step->transitions as $transition)
                        <tr>
                            <td class="py-2 pr-4">{{ $transition->option?->localizedLabel() ?? __('Default') }}</td>
                            <td class="py-2 pr-4">{{ $transition->action_type }}</td>
                            <td class="py-2 pr-4">{{ $transition->targetStep?->localizedQuestion() ?? $transition->targetProcess?->localizedName() ?? '-' }}</td>
                            <td class="py-2"><form method="POST" action="{{ route('loopengine.transitions.destroy', $transition) }}" class="inline">@csrf @method('DELETE')<button class="text-rose-600">{{ __('Delete') }}</button></form></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
