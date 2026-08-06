@extends('layouts.shell')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="mb-6 text-2xl font-bold text-slate-900">{{ $recommendation ? __('Edit Recommendation') : __('New Recommendation') }}</h1>

    <form method="POST" action="{{ $recommendation ? route('auditintelligence.recommendations.update', [$finding, $recommendation]) : route('auditintelligence.recommendations.store', $finding) }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if($recommendation) @method('PUT') @endif

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Issue') }}</label>
            <textarea name="recommendation[issue]" rows="3" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]" required>{{ old('recommendation.issue', $recommendation?->issue) }}</textarea>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Solution') }}</label>
            <textarea name="recommendation[solution]" rows="3" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('recommendation.solution', $recommendation?->solution) }}</textarea>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <x-form-field name="recommendation[responsible]" label="Responsible" :value="old('recommendation.responsible', $recommendation?->responsible)" />
            <x-form-field name="recommendation[related_sop]" label="Related SOP" :value="old('recommendation.related_sop', $recommendation?->related_sop)" />
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Effort') }}</label>
                <select name="recommendation[effort]" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    @foreach(['small','medium','large'] as $effort)
                        <option value="{{ $effort }}" {{ old('recommendation.effort', $recommendation?->effort) == $effort ? 'selected' : '' }}>{{ __($effort) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
                <select name="recommendation[status]" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    @foreach(['pending','accepted','implemented','dismissed'] as $status)
                        <option value="{{ $status }}" {{ old('recommendation.status', $recommendation?->status) == $status ? 'selected' : '' }}>{{ __($status) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ $recommendation ? __('Update') : __('Create') }}</button>
            <a href="{{ route('auditintelligence.findings.show', $finding) }}" class="text-sm text-slate-600 hover:underline">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection
