@extends('layouts.shell')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.audit_questions.create_title') }}</h1>
        <p class="text-sm text-slate-500">{{ $template->name }}</p>
    </div>

    <div class="max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.audits.questions.store') }}" class="space-y-5">
            <input type="hidden" name="template_id" value="{{ $template->id }}">
            @include('admin.audits.questions._form')

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ $pillar ? route('admin.audits.pillars.edit', $pillar) : route('admin.audits.templates.edit', $template) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.audit_questions.create_button') }}</button>
            </div>
        </form>
    </div>
@endsection
