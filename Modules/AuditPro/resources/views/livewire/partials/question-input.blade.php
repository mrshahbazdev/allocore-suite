<div class="mt-4">
    @switch($question->question_type)
        @case('scale_1_to_5')
            <div class="grid grid-cols-5 gap-2" role="radiogroup">
                @foreach (range(0, 4) as $score)
                    <label class="cursor-pointer">
                        <input wire:model="answers.{{ $question->id }}.value" name="question_scale_{{ $question->id }}" type="radio" value="{{ $score }}" class="peer sr-only">
                        <span class="flex h-12 items-center justify-center rounded-xl border border-slate-300 font-bold text-sm text-slate-700 bg-white transition-all hover:bg-slate-50 peer-checked:border-orange-500 peer-checked:bg-orange-500 peer-checked:text-white shadow-sm">{{ $score }}</span>
                    </label>
                @endforeach
            </div>
            <div class="mt-2 flex justify-between text-xs font-semibold text-slate-400"><span>{{ __('Needs attention') }}</span><span>{{ __('Excellent') }}</span></div>
            @break
        @case('yes_no')
            <div class="flex gap-3" role="radiogroup">
                @foreach ([1 => __('Yes'), 0 => __('No')] as $value => $label)
                    <label class="cursor-pointer">
                        <input wire:model="answers.{{ $question->id }}.value" name="question_yesno_{{ $question->id }}" type="radio" value="{{ $value }}" class="peer sr-only">
                        <span class="inline-flex rounded-xl border border-slate-300 px-8 py-2.5 text-sm font-bold text-slate-700 bg-white transition-all hover:bg-slate-50 peer-checked:border-orange-500 peer-checked:bg-orange-500 peer-checked:text-white shadow-sm">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @break
        @case('text_input')
            <textarea wire:model="answers.{{ $question->id }}.value" rows="3" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" placeholder="{{ __('Your answer') }}"></textarea>
            @break
        @case('select')
            <select wire:model="answers.{{ $question->id }}.value" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                <option value="">{{ __('Select an option') }}</option>
                @foreach ($question->options ?? [] as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach
            </select>
            @break
        @case('radio')
            <div class="space-y-2" role="radiogroup">
                @foreach ($question->options ?? [] as $option)
                    <label class="flex items-center gap-3 text-sm font-medium text-slate-700 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input wire:model="answers.{{ $question->id }}.value" name="question_radio_{{ $question->id }}" type="radio" value="{{ $option }}" class="border-slate-300 text-orange-500 focus:ring-orange-500">
                        <span>{{ $option }}</span>
                    </label>
                @endforeach
            </div>
            @break
        @case('checkbox')
            <div class="space-y-2">
                @foreach ($question->options ?? [] as $option)
                    <label class="flex items-center gap-3 text-sm font-medium text-slate-700 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input wire:model="answers.{{ $question->id }}.value" type="checkbox" value="{{ $option }}" class="rounded border-slate-300 text-orange-500 focus:ring-orange-500">
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
    <textarea wire:model="answers.{{ $question->id }}.comment" rows="2" class="mt-1 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500" placeholder="{{ __('Optional note') }}"></textarea>
</div>
