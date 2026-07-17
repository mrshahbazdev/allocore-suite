@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.audit_templates.edit_title', ['name' => $template->name]) }}</h1>
            <p class="text-sm text-slate-500">{{ $template->slug }}</p>
        </div>
        <a href="{{ route('admin.audits.templates.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('Back to templates') }}</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-1 overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('admin.audits.templates.update', $template) }}" class="space-y-5">
                @csrf
                @method('PUT')
                @include('admin.audits.templates._form')

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.audit_templates.save_button') }}</button>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('admin.audit_pillars.title') }} ({{ $template->pillars->count() }})</h2>
                    <a href="{{ route('admin.audits.pillars.create', ['template_id' => $template->id]) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.audit_pillars.add') }}</a>
                </div>

                <div class="space-y-3">
                    @forelse ($template->pillars as $pillar)
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-slate-900">{{ $pillar->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $pillar->questions->count() }} {{ __('admin.audit_questions.title') }} · {{ __('admin.audit_pillars.target') }} {{ $pillar->target_score }}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.audits.pillars.edit', $pillar) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.audits.pillars.destroy', $pillar) }}" onsubmit="return confirm('{{ __('admin.audit_pillars.confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </div>

                            <div class="mt-3 space-y-2">
                                @foreach ($pillar->questions as $question)
                                    <div class="flex items-center justify-between rounded bg-slate-50 px-3 py-2 text-sm">
                                        <div class="text-slate-700">{{ $question->question }}</div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-slate-500">{{ $question->question_type }}</span>
                                            <a href="{{ route('admin.audits.questions.edit', $question) }}" class="text-xs font-medium text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                            <form method="POST" action="{{ route('admin.audits.questions.destroy', $question) }}" onsubmit="return confirm('{{ __('admin.audit_questions.confirm_delete') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-xs font-medium text-rose-600 hover:underline">{{ __('Delete') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                                <a href="{{ route('admin.audits.questions.create', ['template_id' => $template->id, 'pillar_id' => $pillar->id]) }}" class="inline-block text-xs font-medium text-indigo-600 hover:underline">+ {{ __('admin.audit_questions.add') }}</a>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">{{ __('admin.audit_pillars.empty') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
