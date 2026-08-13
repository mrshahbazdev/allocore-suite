<div>
    <div class="mx-auto max-w-4xl">
        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">{{ __('AuditPro assessment') }}</p>
                <h1 class="text-2xl font-bold text-slate-900">{{ __($audit->template->name) }}</h1>
                <p class="text-sm text-slate-500">{{ __('Step :current of :total', ['current' => $currentStep, 'total' => $stepCount]) }}</p>
            </div>
            <button wire:click="saveDraft" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Save draft') }}</button>
        </div>

        <div class="mb-6 h-2 overflow-hidden rounded-full bg-slate-200">
            <div class="h-full rounded-full bg-indigo-600 transition-all" style="width: {{ $stepCount ? ($currentStep / $stepCount) * 100 : 0 }}%"></div>
        </div>

        @if ($pillar)
            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 class="text-xl font-semibold text-slate-900">{{ __($pillar->name) }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __($pillar->description) }}</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @php $questionNumber = 1; @endphp
                    @foreach ($mainQuestions as $mainQuestion)
                        <div class="p-6" wire:key="question-group-{{ $mainQuestion->id }}">
                            <div class="flex gap-2">
                                <span class="font-semibold text-slate-900">{{ $questionNumber++ }}.</span>
                                <div>
                                    <p class="font-medium text-slate-900">{{ __($mainQuestion->question) }} @if ($mainQuestion->is_required)<span class="text-rose-500">*</span>@endif</p>
                                    @if ($mainQuestion->description)<p class="mt-1 text-sm text-slate-500">{{ __($mainQuestion->description) }}</p>@endif
                                </div>
                            </div>

                            @include('auditpro::livewire.partials.question-input', ['question' => $mainQuestion])

                            @if ($mainQuestion->children && $mainQuestion->children->isNotEmpty())
                                <div class="mt-6 space-y-6 border-l-2 border-indigo-100 pl-6">
                                    @foreach ($mainQuestion->children as $followUp)
                                        <div wire:key="question-{{ $followUp->id }}">
                                            <div class="flex gap-2">
                                                <span class="font-semibold text-slate-700">{{ $questionNumber++ }}.</span>
                                                <div>
                                                    <p class="font-medium text-slate-900">{{ __($followUp->question) }} @if ($followUp->is_required)<span class="text-rose-500">*</span>@endif</p>
                                                    @if ($followUp->description)<p class="mt-1 text-sm text-slate-500">{{ __($followUp->description) }}</p>@endif
                                                </div>
                                            </div>
                                            @include('auditpro::livewire.partials.question-input', ['question' => $followUp])
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between border-t border-slate-200 px-6 py-5">
                    <button wire:click="previousStep" @disabled($currentStep === 1) class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-40">{{ __('Previous') }}</button>
                    <button wire:click="nextStep" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        {{ $currentStep === $stepCount ? __('Complete audit') : __('Save and continue') }}
                    </button>
                </div>
            </section>
        @endif
    </div>
</div>
