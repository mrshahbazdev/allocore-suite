@extends('layouts.shell')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="mb-6 text-2xl font-bold text-slate-900">{{ $finding ? __('Edit Finding') : __('New Finding') }}</h1>

    <form method="POST" action="{{ $finding ? route('auditintelligence.findings.update', $finding) : route('auditintelligence.findings.store') }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if($finding) @method('PUT') @endif

        <x-form-field name="finding[title]" label="Title" required :value="old('finding.title', $finding?->title)" />

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
            <textarea name="finding[description]" rows="3" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('finding.description', $finding?->description) }}</textarea>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Risk Level') }}</label>
                <select name="finding[risk_level]" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    @foreach(['low','medium','high','critical'] as $level)
                        <option value="{{ $level }}" {{ old('finding.risk_level', $finding?->risk_level) == $level ? 'selected' : '' }}>{{ __($level) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Priority') }}</label>
                <select name="finding[priority]" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    @foreach(['low','medium','high'] as $priority)
                        <option value="{{ $priority }}" {{ old('finding.priority', $finding?->priority) == $priority ? 'selected' : '' }}>{{ __($priority) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Legal Relevance') }}</label>
                <select name="finding[legal_relevance]" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    @foreach(['low','medium','high'] as $value)
                        <option value="{{ $value }}" {{ old('finding.legal_relevance', $finding?->legal_relevance) == $value ? 'selected' : '' }}>{{ __($value) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Implementation Effort') }}</label>
                <select name="finding[implementation_effort]" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    @foreach(['small','medium','large'] as $effort)
                        <option value="{{ $effort }}" {{ old('finding.implementation_effort', $finding?->implementation_effort) == $effort ? 'selected' : '' }}>{{ __($effort) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Status') }}</label>
            <select name="finding[status]" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                @foreach(['open','in_progress','resolved','accepted'] as $status)
                    <option value="{{ $status }}" {{ old('finding.status', $finding?->status) == $status ? 'selected' : '' }}>{{ __($status) }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-[#ff9200] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e68200]">{{ $finding ? __('Update') : __('Create') }}</button>
            <a href="{{ route('auditintelligence.findings.index') }}" class="text-sm text-slate-600 hover:underline">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection
