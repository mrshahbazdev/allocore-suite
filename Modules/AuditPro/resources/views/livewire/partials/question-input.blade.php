@php
    $currentVal = $answers[$question->id]['value'] ?? null;
@endphp

<div class="mt-4" wire:key="q-input-{{ $question->id }}">
    @switch($question->question_type)
        @case('scale_1_to_5')
            <div class="grid grid-cols-5 gap-2" role="radiogroup">
                @foreach (range(0, 4) as $score)
                    @php $isSelected = ($currentVal !== null && $currentVal !== '' && (string)$currentVal === (string)$score); @endphp
                    <label class="cursor-pointer" wire:key="q-{{ $question->id }}-opt-{{ $score }}">
                        <input wire:model.live="answers.{{ $question->id }}.value" name="question_scale_{{ $question->id }}" type="radio" value="{{ $score }}" class="peer sr-only">
                        <span class="flex h-12 items-center justify-center rounded-xl font-black text-base transition-all duration-150 shadow-sm {{ $isSelected ? 'border-2 border-[#ff9200] bg-[#ff9200] text-white shadow-md ring-2 ring-orange-300' : 'border border-slate-300 bg-white text-slate-700 hover:border-slate-400 hover:bg-slate-50' }} peer-checked:!border-[#ff9200] peer-checked:!bg-[#ff9200] peer-checked:!text-white peer-checked:!shadow-md peer-checked:!ring-2 peer-checked:!ring-orange-300">{{ $score }}</span>
                    </label>
                @endforeach
            </div>
            <div class="mt-2 flex justify-between text-xs font-semibold text-slate-400"><span>{{ __('Needs attention') }}</span><span>{{ __('Excellent') }}</span></div>
            @break
        @case('yes_no')
            <div class="flex gap-3" role="radiogroup">
                @foreach ([1 => __('Yes'), 0 => __('No')] as $value => $label)
                    @php $isSelected = ($currentVal !== null && $currentVal !== '' && (string)$currentVal === (string)$value); @endphp
                    <label class="cursor-pointer" wire:key="q-{{ $question->id }}-yn-{{ $value }}">
                        <input wire:model.live="answers.{{ $question->id }}.value" name="question_yesno_{{ $question->id }}" type="radio" value="{{ $value }}" class="peer sr-only">
                        <span class="inline-flex rounded-xl px-8 py-2.5 text-sm font-bold transition-all duration-150 shadow-sm {{ $isSelected ? 'border-2 border-[#ff9200] bg-[#ff9200] text-white shadow-md ring-2 ring-orange-300' : 'border border-slate-300 bg-white text-slate-700 hover:border-slate-400 hover:bg-slate-50' }} peer-checked:!border-[#ff9200] peer-checked:!bg-[#ff9200] peer-checked:!text-white peer-checked:!shadow-md peer-checked:!ring-2 peer-checked:!ring-orange-300">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @break
        @case('text_input')
            <textarea wire:model.blur="answers.{{ $question->id }}.value" rows="3" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]" placeholder="{{ __('Your answer') }}"></textarea>
            @break
        @case('select')
            <select wire:model.live="answers.{{ $question->id }}.value" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                <option value="">{{ __('Select an option') }}</option>
                @foreach ($question->options ?? [] as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach
            </select>
            @break
        @case('radio')
            <div class="space-y-2" role="radiogroup">
                @foreach ($question->options ?? [] as $option)
                    @php $isSelected = ($currentVal !== null && $currentVal !== '' && (string)$currentVal === (string)$option); @endphp
                    <label class="flex items-center gap-3 text-sm font-medium p-3 rounded-xl border transition-all cursor-pointer {{ $isSelected ? 'border-[#ff9200] bg-orange-50/70 text-orange-950 font-bold ring-1 ring-[#ff9200]' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}" wire:key="q-{{ $question->id }}-rad-{{ $loop->index }}">
                        <input wire:model.live="answers.{{ $question->id }}.value" name="question_radio_{{ $question->id }}" type="radio" value="{{ $option }}" class="border-slate-300 text-[#ff9200] focus:ring-[#ff9200]">
                        <span>{{ $option }}</span>
                    </label>
                @endforeach
            </div>
            @break
        @case('checkbox')
            <div class="space-y-2">
                @foreach ($question->options ?? [] as $option)
                    @php $isChecked = is_array($currentVal) && in_array($option, $currentVal, true); @endphp
                    <label class="flex items-center gap-3 text-sm font-medium p-3 rounded-xl border transition-all cursor-pointer {{ $isChecked ? 'border-[#ff9200] bg-orange-50/70 text-orange-950 font-bold' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}" wire:key="q-{{ $question->id }}-chk-{{ $loop->index }}">
                        <input wire:model.live="answers.{{ $question->id }}.value" type="checkbox" value="{{ $option }}" class="rounded border-slate-300 text-[#ff9200] focus:ring-[#ff9200]">
                        <span>{{ $option }}</span>
                    </label>
                @endforeach
            </div>
            @break
        @case('file_upload')
            <input wire:model="answers.{{ $question->id }}.value" type="file" class="w-full rounded-xl border border-slate-300 bg-white p-2 text-sm">
            @break
    @endswitch
    @error("answers.{$question->id}.value")<p class="mt-2 text-sm text-rose-600 font-medium">{{ $message }}</p>@enderror
</div>

<div class="mt-4">
    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('Optional note') }}</label>
    <textarea wire:model.blur="answers.{{ $question->id }}.comment" rows="2" class="mt-1 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]" placeholder="{{ __('Optional note') }}"></textarea>
</div>
