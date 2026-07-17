@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('admin.audit_pillars.edit_title', ['name' => $pillar->name]) }}</h1>
            <p class="text-sm text-slate-500">{{ $pillar->template->name }}</p>
        </div>
        <a href="{{ route('admin.audits.templates.edit', $pillar->template) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ __('admin.audit_pillars.back_to_template') }}</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-1 overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('admin.audits.pillars.update', $pillar) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                    <input name="name" value="{{ old('name', $pillar->name) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                    <textarea name="description" rows="3" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $pillar->description) }}</textarea>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('admin.audit_pillars.icon') }}</label>
                        <input name="icon" value="{{ old('icon', $pillar->icon) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('admin.audit_pillars.target') }}</label>
                        <input name="target_score" type="number" step="0.1" min="0" max="10" value="{{ old('target_score', $pillar->target_score) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('admin.audit_pillars.position') }}</label>
                    <input name="position" type="number" min="0" value="{{ old('position', $pillar->position) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.audit_pillars.save_button') }}</button>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2 overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('admin.audit_questions.title') }} ({{ $pillar->questions->count() }})</h2>
                <a href="{{ route('admin.audits.questions.create', ['template_id' => $pillar->template_id, 'pillar_id' => $pillar->id]) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('admin.audit_questions.add') }}</a>
            </div>

            <div class="space-y-2">
                @forelse ($pillar->questions as $question)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3 text-sm">
                        <div>
                            <div class="font-medium text-slate-900">{{ $question->question }}</div>
                            <div class="text-xs text-slate-500">{{ $question->question_type }} · {{ __('admin.audit_questions.weight') }} {{ $question->weight }} · {{ $question->is_required ? __('Required') : __('Optional') }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.audits.questions.edit', $question) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.audits.questions.destroy', $question) }}" onsubmit="return confirm('{{ __('admin.audit_questions.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">{{ __('admin.audit_questions.empty') }}</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
