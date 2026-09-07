@php $title = $isEdit ? __('Edit Question Mapping') : __('Create Question Mapping'); @endphp
@extends('layouts.shell')

@section('content')
    @include('bookintelligence::partials.nav')

    <div class="mx-auto max-w-4xl space-y-6" data-no-navigate>
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('bookintelligence.questions.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    &larr; {{ __('Back to Questions & FAQs') }}
                </a>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $isEdit ? __('Edit Question Mapping') : __('New Question Mapping') }}</h1>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $isEdit ? route('bookintelligence.questions.update', $mapping->id) : route('bookintelligence.questions.store') }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="space-y-4">
                <!-- Book selection -->
                <div>
                    <label for="book_id" class="block text-sm font-semibold text-slate-900">{{ __('Source Book') }} *</label>
                    <select id="book_id" name="book_id" required class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                        <option value="">{{ __('-- Select Source Book --') }}</option>
                        @foreach ($books as $book)
                            <option value="{{ $book->id }}" @selected((string) old('book_id', $mapping->book_id) === (string) $book->id)>
                                {{ $book->title }} ({{ $book->author?->name ?? 'Unknown' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Question -->
                <div>
                    <label for="question" class="block text-sm font-semibold text-slate-900">{{ __('Question (What the book answers)') }} *</label>
                    <input type="text" id="question" name="question" value="{{ old('question', $mapping->question) }}" required placeholder="e.g. How can I scale our B2B outbound sales engine?" class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                </div>

                <!-- Answer excerpt -->
                <div>
                    <label for="answer_excerpt" class="block text-sm font-semibold text-slate-900">{{ __('Answer Excerpt / Solution Summary') }} *</label>
                    <textarea id="answer_excerpt" name="answer_excerpt" rows="4" required placeholder="Concise 2-4 sentence synthesis of the book's direct methodology..." class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">{{ old('answer_excerpt', $mapping->answer_excerpt) }}</textarea>
                </div>

                <!-- Problem statement -->
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="problem_statement" class="block text-sm font-semibold text-slate-900">{{ __('Business Problem Solved') }}</label>
                        <input type="text" id="problem_statement" name="problem_statement" value="{{ old('problem_statement', $mapping->problem_statement) }}" placeholder="e.g. Unpredictable revenue and low SDR pipeline" class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                    <div>
                        <label for="category" class="block text-sm font-semibold text-slate-900">{{ __('Category / Domain') }}</label>
                        <input type="text" id="category" name="category" value="{{ old('category', $mapping->category) }}" placeholder="e.g. Sales, Finance, Leadership" class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                </div>

                <!-- When to read trigger -->
                <div>
                    <label for="when_to_read_trigger" class="block text-sm font-semibold text-slate-900">{{ __('When to Read Trigger') }}</label>
                    <input type="text" id="when_to_read_trigger" name="when_to_read_trigger" value="{{ old('when_to_read_trigger', $mapping->when_to_read_trigger) }}" placeholder="e.g. When scaling past 10 sales reps and pipeline conversion drops below 15%" class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                </div>

                <!-- Allocore 3-Card Guidance Linkage Section -->
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span>🎯</span> {{ __('Allocore Guidance & Tool Linkage (For Personal Coach & Audits)') }}
                    </h3>
                    <p class="text-xs text-slate-500">
                        {{ __('Map this question to a specific Allocore Tool and Glossary Knowledge Term so the AI Coach can present 3-card guidance when an audit question is answered negatively.') }}
                    </p>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="module_key" class="block text-xs font-semibold text-slate-700">{{ __('Allocore Tool (Module)') }}</label>
                            <select id="module_key" name="module_key" class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                                <option value="">{{ __('-- None --') }}</option>
                                @foreach ($modules as $mod)
                                    <option value="{{ $mod->key }}" @selected(old('module_key', $mapping->module_key) === $mod->key)>
                                        {{ $mod->name }} ({{ $mod->key }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="glossary_term_id" class="block text-xs font-semibold text-slate-700">{{ __('Knowledge Base Term') }}</label>
                            <select id="glossary_term_id" name="glossary_term_id" class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                                <option value="">{{ __('-- None --') }}</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" @selected((string) old('glossary_term_id', $mapping->glossary_term_id) === (string) $term->id)>
                                        {{ $term->term }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if ($auditQuestions->isNotEmpty())
                        <div>
                            <label for="audit_question_id" class="block text-xs font-semibold text-slate-700">{{ __('Audit Question Link') }}</label>
                            <select id="audit_question_id" name="audit_question_id" class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                                <option value="">{{ __('-- None / Auto-detect --') }}</option>
                                @foreach ($auditQuestions as $aq)
                                    <option value="{{ $aq->id }}" @selected((string) old('audit_question_id', $mapping->audit_question_id) === (string) $aq->id)>
                                        {{ Str::limit($aq->question, 80) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <!-- Priority & Status -->
                <div class="flex items-center gap-6 pt-2">
                    <div class="w-32">
                        <label for="priority" class="block text-xs font-semibold text-slate-700">{{ __('Priority (1–10)') }}</label>
                        <input type="number" id="priority" name="priority" min="1" max="10" value="{{ old('priority', $mapping->priority ?? 1) }}" class="mt-1 w-full rounded-xl border-slate-300 text-sm focus:border-[#ff9200] focus:ring-[#ff9200]">
                    </div>
                    <div class="flex items-center gap-2 pt-5">
                        <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $mapping->is_active ?? true)) class="rounded border-slate-300 text-[#ff9200] focus:ring-[#ff9200]">
                        <label for="is_active" class="text-sm font-medium text-slate-700">{{ __('Active in Question Engine & Search') }}</label>
                    </div>
                </div>
            </div>

            <!-- Submit buttons -->
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
                <a href="{{ route('bookintelligence.questions.index') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="rounded-xl bg-[#ff9200] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">
                    {{ $isEdit ? __('Save Changes') : __('Create Mapping') }}
                </button>
            </div>
        </form>
    </div>
@endsection
