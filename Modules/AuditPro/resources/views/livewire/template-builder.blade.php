<div>
    @include('auditpro::partials.nav')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('audit.templates') }}" class="text-sm font-medium text-indigo-600 hover:underline">← {{ __('Templates') }}</a>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $template->name }}</h1>
            <p class="text-sm text-slate-500">{{ $template->description }}</p>
        </div>
        <button wire:click="createPillar" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Add pillar') }}</button>
    </div>

    <div class="space-y-5">
        @forelse ($pillars as $pillar)
            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold text-slate-900">{{ $pillar->position }}. {{ $pillar->name }}</h2>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ __('Target :score', ['score' => $pillar->target_score]) }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ $pillar->description }}</p>
                    </div>
                    <div class="flex gap-3 text-sm">
                        <button wire:click="createQuestion({{ $pillar->id }})" class="font-medium text-indigo-600 hover:underline">{{ __('Add question') }}</button>
                        <button wire:click="editPillar({{ $pillar->id }})" class="text-slate-600 hover:underline">{{ __('Edit') }}</button>
                        <button wire:click="deletePillar({{ $pillar->id }})" wire:confirm="{{ __('Delete this pillar and its questions?') }}" class="text-rose-600 hover:underline">{{ __('Delete') }}</button>
                    </div>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($pillar->questions as $question)
                        <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex gap-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-700">{{ $question->position }}</span>
                                <div>
                                    <p class="font-medium text-slate-800">{{ $question->question }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $question->description }}</p>
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-500">
                                        <span class="rounded bg-slate-100 px-2 py-1">{{ str_replace('_', ' ', $question->question_type) }}</span>
                                        <span class="rounded bg-slate-100 px-2 py-1">{{ __('Weight :weight', ['weight' => $question->weight]) }}</span>
                                        @if ($question->is_required)<span class="rounded bg-amber-50 px-2 py-1 text-amber-700">{{ __('Required') }}</span>@endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex shrink-0 gap-3 text-sm">
                                <button wire:click="editQuestion({{ $question->id }})" class="text-indigo-600 hover:underline">{{ __('Edit') }}</button>
                                <button wire:click="deleteQuestion({{ $question->id }})" wire:confirm="{{ __('Delete this question?') }}" class="text-rose-600 hover:underline">{{ __('Delete') }}</button>
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">{{ __('No questions in this pillar.') }}</p>
                    @endforelse
                </div>
            </section>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">{{ __('Add a pillar to begin building this template.') }}</div>
        @endforelse
    </div>

    @if ($showPillarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <form wire:submit="savePillar" class="w-full max-w-xl space-y-4 rounded-xl bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-slate-900">{{ $pillarId ? __('Edit pillar') : __('New pillar') }}</h2>
                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('Name') }}</label>
                    <input wire:model="pillarName" class="mt-1 w-full rounded-lg border-slate-300">
                    @error('pillarName')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                    <textarea wire:model="pillarDescription" rows="3" class="mt-1 w-full rounded-lg border-slate-300"></textarea>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700">{{ __('Icon name') }}</label>
                        <input wire:model="pillarIcon" class="mt-1 w-full rounded-lg border-slate-300">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">{{ __('Target score') }}</label>
                        <input wire:model="pillarTargetScore" type="number" min="1" max="5" step="0.1" class="mt-1 w-full rounded-lg border-slate-300">
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="$set('showPillarModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">{{ __('Cancel') }}</button>
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Save pillar') }}</button>
                </div>
            </form>
        </div>
    @endif

    @if ($showQuestionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 p-4">
            <form wire:submit="saveQuestion" class="my-8 w-full max-w-2xl space-y-4 rounded-xl bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-slate-900">{{ $questionId ? __('Edit question') : __('New question') }}</h2>
                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('Question') }}</label>
                    <textarea wire:model="questionText" rows="2" class="mt-1 w-full rounded-lg border-slate-300"></textarea>
                    @error('questionText')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                    <textarea wire:model="questionDescription" rows="2" class="mt-1 w-full rounded-lg border-slate-300"></textarea>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700">{{ __('Pillar') }}</label>
                        <select wire:model="questionPillarId" class="mt-1 w-full rounded-lg border-slate-300">
                            @foreach ($pillars as $pillar)<option value="{{ $pillar->id }}">{{ $pillar->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">{{ __('Answer type') }}</label>
                        <select wire:model.live="questionType" class="mt-1 w-full rounded-lg border-slate-300">
                            @foreach (['scale_1_to_5', 'yes_no', 'text_input', 'select', 'radio', 'checkbox', 'file_upload'] as $type)
                                <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @if (in_array($questionType, ['select', 'radio', 'checkbox']))
                    <div>
                        <label class="text-sm font-medium text-slate-700">{{ __('Options, comma separated') }}</label>
                        <input wire:model="questionOptions" class="mt-1 w-full rounded-lg border-slate-300">
                    </div>
                @endif
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700">{{ __('Weight') }}</label>
                        <input wire:model="questionWeight" type="number" min="0.1" max="10" step="0.1" class="mt-1 w-full rounded-lg border-slate-300">
                    </div>
                    <label class="mt-7 flex items-center gap-2 text-sm text-slate-700">
                        <input wire:model="questionIsRequired" type="checkbox" class="rounded border-slate-300 text-indigo-600">
                        {{ __('Required question') }}
                    </label>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('Recommendation when score is low') }}</label>
                    <textarea wire:model="questionFailureRecommendation" rows="2" class="mt-1 w-full rounded-lg border-slate-300"></textarea>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700">{{ __('Depends on question') }}</label>
                        <select wire:model="questionDependsOnId" class="mt-1 w-full rounded-lg border-slate-300">
                            <option value="">{{ __('No dependency') }}</option>
                            @foreach ($dependencyQuestions->where('id', '!=', $questionId) as $dependency)
                                <option value="{{ $dependency->id }}">{{ \Illuminate\Support\Str::limit($dependency->question, 55) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">{{ __('Required answer') }}</label>
                        <input wire:model="questionDependsOnAnswer" class="mt-1 w-full rounded-lg border-slate-300">
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="$set('showQuestionModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">{{ __('Cancel') }}</button>
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Save question') }}</button>
                </div>
            </form>
        </div>
    @endif
</div>
