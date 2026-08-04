@props(['name', 'label', 'type' => 'text', 'step' => null, 'placeholder' => '', 'required' => false, 'helper' => null])

<div>
    <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-slate-700">
        {{ __($label) }}
        @if ($required)
            <span class="text-rose-500">*</span>
        @endif
    </label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name) }}"
        placeholder="{{ $placeholder }}"
        @if ($step) step="{{ $step }}" @endif
        @if ($required) required @endif
        class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]"
    >
    @if ($helper)
        <p class="mt-1 text-xs text-slate-500">{{ __($helper) }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
    @enderror
</div>
