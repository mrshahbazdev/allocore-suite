@extends('layouts.shell')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Edit :pillar Mini-Audit Questions', ['pillar' => $pillar]) }}</h1>
            <p class="text-sm text-slate-500">{{ __('Each group has one main question plus up to five follow-up questions. These are used when a user starts a small audit for this pillar.') }}</p>
        </div>
        <a href="{{ route('admin.audits.pillar-questions.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Back') }}</a>
    </div>

    <form method="POST" action="{{ route('admin.audits.pillar-questions.update', $pillar) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div id="groups-container" class="space-y-6">
            @forelse ($groups as $groupIndex => $group)
                <div class="group-row rounded-xl border border-slate-200 bg-white p-5 shadow-sm" data-index="{{ $groupIndex }}">
                    <div class="mb-4 flex items-center justify-between">
                        <span class="group-number text-sm font-semibold uppercase tracking-wider text-indigo-600">{{ __('Question group') }} #{{ $groupIndex + 1 }}</span>
                        <button type="button" class="remove-group text-sm font-medium text-rose-600 hover:text-rose-800">{{ __('Remove group') }}</button>
                    </div>

                    <div class="space-y-4 rounded-lg bg-slate-50 p-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Main question') }}</label>
                            <textarea name="groups[{{ $groupIndex }}][main][question]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm" required>{{ old("groups.$groupIndex.main.question", $group['main']->question) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                            <textarea name="groups[{{ $groupIndex }}][main][description]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">{{ old("groups.$groupIndex.main.description", $group['main']->description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('Recommendation on failure') }}</label>
                            <textarea name="groups[{{ $groupIndex }}][main][recommendation]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">{{ old("groups.$groupIndex.main.recommendation", $group['main']->recommendation) }}</textarea>
                        </div>
                    </div>

                    <div class="follow-ups mt-4 space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Follow-up questions') }}</p>
                        @foreach ($group['follow_ups'] as $followUpIndex => $followUp)
                            <div class="follow-up-row rounded-lg border border-slate-200 p-3">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="follow-up-number text-xs font-medium text-slate-500">{{ __('Follow-up') }} #{{ $followUpIndex + 1 }}</span>
                                    <button type="button" class="remove-follow-up text-xs font-medium text-rose-600 hover:text-rose-800">{{ __('Remove') }}</button>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700">{{ __('Question') }}</label>
                                        <textarea name="groups[{{ $groupIndex }}][follow_ups][{{ $followUpIndex }}][question]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm" required>{{ old("groups.$groupIndex.follow_ups.$followUpIndex.question", $followUp->question) }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700">{{ __('Description') }}</label>
                                        <textarea name="groups[{{ $groupIndex }}][follow_ups][{{ $followUpIndex }}][description]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">{{ old("groups.$groupIndex.follow_ups.$followUpIndex.description", $followUp->description) }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700">{{ __('Recommendation on failure') }}</label>
                                        <textarea name="groups[{{ $groupIndex }}][follow_ups][{{ $followUpIndex }}][recommendation]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">{{ old("groups.$groupIndex.follow_ups.$followUpIndex.recommendation", $followUp->recommendation) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="add-follow-up mt-3 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">{{ __('Add follow-up') }}</button>
                </div>
            @empty
                <p class="text-center text-slate-500" id="empty-state">{{ __('No question groups yet. Add the first group below.') }}</p>
            @endforelse
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="button" id="add-group" class="rounded-lg border border-indigo-300 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">{{ __('Add question group') }}</button>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Save questions') }}</button>
        </div>
    </form>

    <script>
        const container = document.getElementById('groups-container');
        const addGroupBtn = document.getElementById('add-group');
        const emptyState = document.getElementById('empty-state');

        function renumber() {
            container.querySelectorAll('.group-row').forEach((group, groupIdx) => {
                group.dataset.index = groupIdx;
                group.querySelector('.group-number').textContent = '{{ __('Question group') }} #' + (groupIdx + 1);

                group.querySelectorAll('textarea[name^="groups["]').forEach(input => {
                    const name = input.name;
                    const suffixMatch = name.match(/^groups\[\d+\](.*)$/);
                    if (suffixMatch) {
                        input.name = 'groups[' + groupIdx + ']' + suffixMatch[1];
                    }
                });

                group.querySelectorAll('.follow-up-row').forEach((followUp, followIdx) => {
                    followUp.querySelector('.follow-up-number').textContent = '{{ __('Follow-up') }} #' + (followIdx + 1);
                    followUp.querySelectorAll('textarea[name*="[follow_ups]["]').forEach(input => {
                        const name = input.name;
                        const suffixMatch = name.match(/^groups\[\d+\]\[follow_ups\]\[\d+\](.*)$/);
                        if (suffixMatch) {
                            input.name = 'groups[' + groupIdx + '][follow_ups][' + followIdx + ']' + suffixMatch[1];
                        }
                    });
                });
            });

            if (emptyState) {
                emptyState.style.display = container.querySelectorAll('.group-row').length > 0 ? 'none' : 'block';
            }
        }

        function createFollowUp(groupIndex, followUpIndex) {
            const div = document.createElement('div');
            div.className = 'follow-up-row rounded-lg border border-slate-200 p-3';
            div.innerHTML = `
                <div class="mb-2 flex items-center justify-between">
                    <span class="follow-up-number text-xs font-medium text-slate-500">{{ __('Follow-up') }} #${followUpIndex + 1}</span>
                    <button type="button" class="remove-follow-up text-xs font-medium text-rose-600 hover:text-rose-800">{{ __('Remove') }}</button>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">{{ __('Question') }}</label>
                        <textarea name="groups[${groupIndex}][follow_ups][${followUpIndex}][question]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm" required></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700">{{ __('Description') }}</label>
                        <textarea name="groups[${groupIndex}][follow_ups][${followUpIndex}][description]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700">{{ __('Recommendation on failure') }}</label>
                        <textarea name="groups[${groupIndex}][follow_ups][${followUpIndex}][recommendation]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"></textarea>
                    </div>
                </div>
            `;
            return div;
        }

        function createGroup(groupIndex) {
            const div = document.createElement('div');
            div.className = 'group-row rounded-xl border border-slate-200 bg-white p-5 shadow-sm';
            div.dataset.index = groupIndex;
            div.innerHTML = `
                <div class="mb-4 flex items-center justify-between">
                    <span class="group-number text-sm font-semibold uppercase tracking-wider text-indigo-600">{{ __('Question group') }} #${groupIndex + 1}</span>
                    <button type="button" class="remove-group text-sm font-medium text-rose-600 hover:text-rose-800">{{ __('Remove group') }}</button>
                </div>

                <div class="space-y-4 rounded-lg bg-slate-50 p-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Main question') }}</label>
                        <textarea name="groups[${groupIndex}][main][question]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm" required></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                        <textarea name="groups[${groupIndex}][main][description]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Recommendation on failure') }}</label>
                        <textarea name="groups[${groupIndex}][main][recommendation]" rows="2" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"></textarea>
                    </div>
                </div>

                <div class="follow-ups mt-4 space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Follow-up questions') }}</p>
                </div>

                <button type="button" class="add-follow-up mt-3 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">{{ __('Add follow-up') }}</button>
            `;
            return div;
        }

        addGroupBtn.addEventListener('click', () => {
            const groups = container.querySelectorAll('.group-row');
            const nextIndex = groups.length ? parseInt(groups[groups.length - 1].dataset.index) + 1 : 0;
            container.appendChild(createGroup(nextIndex));
            renumber();
        });

        container.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-group')) {
                e.target.closest('.group-row').remove();
                renumber();
            }

            if (e.target.classList.contains('remove-follow-up')) {
                e.target.closest('.follow-up-row').remove();
                renumber();
            }

            if (e.target.classList.contains('add-follow-up')) {
                const group = e.target.closest('.group-row');
                const followUps = group.querySelectorAll('.follow-up-row');
                const nextFollowIndex = followUps.length ? followUps.length : 0;
                group.querySelector('.follow-ups').appendChild(createFollowUp(group.dataset.index, nextFollowIndex));
                renumber();
            }
        });
    </script>
@endsection
