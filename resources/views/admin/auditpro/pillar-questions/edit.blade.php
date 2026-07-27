@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Edit :pillar Mini-Audit Questions', ['pillar' => $pillar]) }}</h1>
            <p class="text-sm text-slate-500">{{ __('These questions are used when a user starts a small audit for this pillar.') }}</p>
        </div>
        <a href="{{ route('admin.audits.pillar-questions.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Back') }}</a>
    </div>

    <form method="POST" action="{{ route('admin.audits.pillar-questions.update', $pillar) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div id="questions-container" class="space-y-4">
            @forelse ($questions as $index => $question)
                <div class="question-row rounded-xl border border-slate-200 bg-white p-5 shadow-sm" data-index="{{ $index }}">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="question-number text-sm font-semibold text-slate-500">{{ __('Question') }} #{{ $index + 1 }}</span>
                        <button type="button" class="remove-row text-sm font-medium text-rose-600 hover:text-rose-800">{{ __('Remove') }}</button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Question') }}</label>
                            <textarea name="questions[{{ $index }}][question]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm" required>{{ old("questions.$index.question", $question->question) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                            <textarea name="questions[{{ $index }}][description]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">{{ old("questions.$index.description", $question->description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Recommendation on failure') }}</label>
                            <textarea name="questions[{{ $index }}][recommendation]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">{{ old("questions.$index.recommendation", $question->recommendation) }}</textarea>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-slate-500" id="empty-state">{{ __('No questions yet. Add the first one below.') }}</p>
            @endforelse
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="button" id="add-question" class="rounded-lg border border-indigo-300 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">{{ __('Add question') }}</button>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Save questions') }}</button>
        </div>
    </form>

    <script>
        const container = document.getElementById('questions-container');
        const addBtn = document.getElementById('add-question');
        const emptyState = document.getElementById('empty-state');

        function renumber() {
            container.querySelectorAll('.question-row').forEach((row, idx) => {
                row.dataset.index = idx;
                row.querySelector('.question-number').textContent = '{{ __('Question') }} #' + (idx + 1);
                row.querySelectorAll('textarea').forEach(input => {
                    const suffix = input.name.replace(/^questions\[\d+\]/, '').replace(/\]$/, '');
                    input.name = 'questions[' + idx + ']' + suffix + (input.name.endsWith(']') ? ']' : '');
                });
            });

            if (container.querySelectorAll('.question-row').length > 0 && emptyState) {
                emptyState.style.display = 'none';
            }
        }

        function createQuestionRow(index) {
            const div = document.createElement('div');
            div.className = 'question-row rounded-xl border border-slate-200 bg-white p-5 shadow-sm';
            div.dataset.index = index;
            div.innerHTML = `
                <div class="mb-3 flex items-center justify-between">
                    <span class="question-number text-sm font-semibold text-slate-500">{{ __('Question') }} #${index + 1}</span>
                    <button type="button" class="remove-row text-sm font-medium text-rose-600 hover:text-rose-800">{{ __('Remove') }}</button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Question') }}</label>
                        <textarea name="questions[${index}][question]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm" required></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                        <textarea name="questions[${index}][description]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Recommendation on failure') }}</label>
                        <textarea name="questions[${index}][recommendation]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"></textarea>
                    </div>
                </div>
            `;
            return div;
        }

        addBtn.addEventListener('click', () => {
            const rows = container.querySelectorAll('.question-row');
            const nextIndex = rows.length ? parseInt(rows[rows.length - 1].dataset.index) + 1 : 0;
            container.appendChild(createQuestionRow(nextIndex));
        });

        container.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('.question-row').remove();
                renumber();
            }
        });
    </script>
@endsection
