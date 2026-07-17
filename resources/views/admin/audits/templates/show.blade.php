@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $template->name }}</h1>
            <p class="text-sm text-slate-500">{{ $template->description }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.audits.templates.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to templates') }}</a>
            <a href="{{ route('admin.audits.templates.edit', $template) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Edit') }}</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Details') }}</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Slug') }}</dt><dd class="font-medium text-slate-900">{{ $template->slug }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Team') }}</dt><dd class="font-medium text-slate-900">{{ $template->team?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('admin.audit_templates.default') }}</dt><dd class="font-medium text-slate-900">{{ $template->is_default ? __('Yes') : __('No') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Pillars') }}</dt><dd class="font-medium text-slate-900">{{ $template->pillars->count() }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Questions') }}</dt><dd class="font-medium text-slate-900">{{ $template->questions->count() }}</dd></div>
            </dl>
        </div>

        <div class="lg:col-span-2 space-y-6">
            @foreach ($template->pillars as $pillar)
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ $pillar->name }}</h3>
                    <p class="text-sm text-slate-500 mb-4">{{ $pillar->description }}</p>

                    <div class="space-y-2">
                        @forelse ($pillar->questions as $question)
                            <div class="rounded-lg bg-slate-50 p-3 text-sm">
                                <div class="font-medium text-slate-900">{{ $question->question }}</div>
                                <div class="text-xs text-slate-500">{{ $question->question_type }} · {{ __('admin.audit_questions.weight') }} {{ $question->weight }}</div>
                            </div>
                        @empty
                            <div class="text-sm text-slate-500">{{ __('admin.audit_questions.empty') }}</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
