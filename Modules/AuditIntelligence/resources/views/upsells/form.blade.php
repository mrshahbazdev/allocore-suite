@extends('layouts.shell')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="mb-6 text-2xl font-bold text-slate-900">{{ $upsell ? __('Edit Upsell') : __('New Upsell') }}</h1>

    <form method="POST" action="{{ $upsell ? route('auditintelligence.upsells.update', [$finding, $upsell]) : route('auditintelligence.upsells.store', $finding) }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if($upsell) @method('PUT') @endif

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Type') }}</label>
                <select name="upsell[type]" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    @foreach(['training','consultant','module','provider'] as $type)
                        <option value="{{ $type }}" {{ old('upsell.type', $upsell?->type) == $type ? 'selected' : '' }}>{{ __($type) }}</option>
                    @endforeach
                </select>
            </div>
            <x-form-field name="upsell[name]" label="Name" required :value="old('upsell.name', $upsell?->name)" />
        </div>

        <x-form-field name="upsell[link]" label="Link" :value="old('upsell.link', $upsell?->link)" />

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
            <textarea name="upsell[description]" rows="3" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('upsell.description', $upsell?->description) }}</textarea>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ $upsell ? __('Update') : __('Create') }}</button>
            <a href="{{ route('auditintelligence.findings.show', $finding) }}" class="text-sm text-slate-600 hover:underline">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection
