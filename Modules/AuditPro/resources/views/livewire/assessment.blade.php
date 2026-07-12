<div>
    <div class="mx-auto max-w-4xl">
        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('AuditPro assessment') }}</p>
                <h1 class="text-2xl font-bold text-slate-900">{{ $audit->template->name }}</h1>
                <p class="text-sm text-slate-500">{{ __('Step :current of :total', ['current' => $currentStep, 'total' => $stepCount]) }}</p>
            </div>
            <button wire:click="saveDraft" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Save draft') }}</button>
        </div>

        <div class="mb-6 h-2 overflow-hidden rounded-full bg-slate-200">
            <div class="h-full rounded-full bg-indigo-600 transition-all" style="width: {{ $stepCount ? ($currentStep / $stepCount) * 100 : 0 }}%"></div>
        </div>

        @if ($pillar)
            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 class="text-xl font-semibold text-slate-900">{{ $pillar->name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $pillar->description }}</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ($questions as $question)
                        <div class="p-6" wire:key="question-{{ $question->id }}">
                            <div class="flex gap-2">
                                <span class="font-semibold text-slate-900">{{ $loop->iteration }}.</span>
                                <div>
                                    <p class="font-medium text-slate-900">{{ $question->question }} @if ($question->is_required)<span class="text-rose-500">*</span>@endif</p>
                                    @if ($question->description)<p class="mt-1 text-sm text-slate-500">{{ $question->description }}</p>@endif
                                </div>
                            </div>

                            <div class="mt-4">
                                @switch($question->question_type)
                                    @case('scale_1_to_5')
                                        <div class="grid grid-cols-5 gap-2">
                                            @foreach (range(1, 5) as $score)
                                                <label class="cursor-pointer">
                                                    <input wire:model="answers.{{ $question->id }}.value" type="radio" value="{{ $score }}" class="peer sr-only">
                                                    <span class="flex h-12 items-center justify-center rounded-lg border border-slate-300 font-semibold text-slate-600 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white">{{ $score }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <div class="mt-2 flex justify-between text-xs text-slate-400"><span>{{ __('Needs attention') }}</span><span>{{ __('Excellent') }}</span></div>
                                        @break
                                    @case('yes_no')
                                        <div class="flex gap-3">
                                            @foreach ([1 => __('Yes'), 0 => __('No')] as $value => $label)
                                                <label class="cursor-pointer">
                                                    <input wire:model="answers.{{ $question->id }}.value" type="radio" value="{{ $value }}" class="peer sr-only">
                                                    <span class="inline-flex rounded-lg border border-slate-300 px-6 py-2 text-sm font-medium text-slate-600 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @break
                                    @case('text_input')
                                        <textarea wire:model="answers.{{ $question->id }}.value" rows="3" class="w-full rounded-lg border-slate-300" placeholder="{{ __('Your answer') }}"></textarea>
                                        @break
                                    @case('select')
                                        <select wire:model="answers.{{ $question->id }}.value" class="w-full rounded-lg border-slate-300">
                                            <option value="">{{ __('Select an option') }}</option>
                                            @foreach ($question->options ?? [] as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach
                                        </select>
                                        @break
                                    @case('radio')
                                        <div class="space-y-2">
                                            @foreach ($question->options ?? [] as $option)
                                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                                    <input wire:model="answers.{{ $question->id }}.value" type="radio" value="{{ $option }}" class="border-slate-300 text-indigo-600">
                                                    {{ $option }}
                                                </label>
                                            @endforeach
                                        </div>
                                        @break
                                    @case('checkbox')
                                        <div class="space-y-2">
                                            @foreach ($question->options ?? [] as $option)
                                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                                    <input wire:model="answers.{{ $question->id }}.value" type="checkbox" value="{{ $option }}" class="rounded border-slate-300 text-indigo-600">
                                                    {{ $option }}
                                                </label>
                                            @endforeach
                                        </div>
                                        @break
                                    @case('file_upload')
                                        <input wire:model="answers.{{ $question->id }}.value" type="file" class="w-full rounded-lg border border-slate-300 bg-white p-2 text-sm">
                                        @break
                                @endswitch
                                @error("answers.{$question->id}.value")<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="mt-4">
                                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Optional note') }}</label>
                                <textarea wire:model="answers.{{ $question->id }}.comment" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between border-t border-slate-200 px-6 py-5">
                    <button wire:click="previousStep" @disabled($currentStep === 1) class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-40">{{ __('Previous') }}</button>
                    <button wire:click="nextStep" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        {{ $currentStep === $stepCount ? __('Complete audit') : __('Save and continue') }}
                    </button>
                </div>
            </section>
        @endif
    </div>
</div>
