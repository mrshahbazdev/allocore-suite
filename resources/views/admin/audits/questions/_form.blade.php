@csrf

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('admin.audit_questions.pillar') }}</label>
    <select name="pillar_id" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        @foreach ($pillars as $p)
            <option value="{{ $p->id }}" @selected(old('pillar_id', $question->pillar_id ?? ($pillar->id ?? '')) == $p->id)>{{ $p->name }}</option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('admin.audit_questions.question') }}</label>
    <input name="question" value="{{ old('question', $question->question ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
    <textarea name="description" rows="2" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $question->description ?? '') }}</textarea>
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('admin.audit_questions.type') }}</label>
        <select name="question_type" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach (['scale_1_to_5' => 'Scale 1-5', 'yes_no' => 'Yes / No', 'text' => 'Text', 'multiple_choice' => 'Multiple choice', 'number' => 'Number', 'file' => 'File'] as $key => $label)
                <option value="{{ $key }}" @selected(old('question_type', $question->question_type ?? 'scale_1_to_5') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('admin.audit_questions.weight') }}</label>
        <input name="weight" type="number" step="0.01" min="0" max="1000" value="{{ old('weight', $question->weight ?? 1) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
    </div>
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('admin.audit_questions.position') }}</label>
        <input name="position" type="number" min="0" value="{{ old('position', $question->position ?? 0) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div class="flex items-center gap-2 pt-7">
        <input id="is_required" name="is_required" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_required', $question->is_required ?? true))>
        <label for="is_required" class="text-sm font-medium text-slate-700">{{ __('admin.audit_questions.required') }}</label>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('admin.audit_questions.failure_recommendation') }}</label>
    <textarea name="failure_recommendation" rows="2" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('failure_recommendation', $question->failure_recommendation ?? '') }}</textarea>
</div>

<div class="rounded-2xl border border-indigo-100 bg-indigo-50/40 p-5 space-y-4">
    <div class="flex items-center gap-2">
        <span class="text-sm font-bold text-indigo-900">🎯 {{ __('Lösungs- & Empfehlungszuweisung (Audit-Lösungen)') }}</span>
    </div>
    <p class="text-xs text-slate-500">{{ __('Wählen Sie genau aus, welches Tool, Buch, Blogartikel oder Glossar-Begriff dem Nutzer bei dieser Frage empfohlen wird.') }}</p>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">🛠️ {{ __('Empfohlenes Plattform-Tool') }}</label>
            <select name="recommended_module_key" class="mt-1.5 block w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('— Automatisch ableiten / Kein Tool —') }}</option>
                @foreach ($modules as $module)
                    <option value="{{ $module->key }}" @selected(old('recommended_module_key', $question->recommended_module_key ?? '') == $module->key)>{{ $module->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">📚 {{ __('Empfohlenes Fachbuch (BookIntelligence)') }}</label>
            <select name="recommended_book_id" class="mt-1.5 block w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('— Kein Fachbuch / Automatisch ableiten —') }}</option>
                @foreach ($books as $b)
                    <option value="{{ $b->id }}" @selected(old('recommended_book_id', $question->recommended_book_id ?? '') == $b->id)>{{ $b->title }} ({{ $b->author?->name ?? 'Buch' }})</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">📰 {{ __('Empfohlener Blog- / Fachartikel (CMS)') }}</label>
            <select name="recommended_post_id" class="mt-1.5 block w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('— Kein Blogartikel zuweisen —') }}</option>
                @foreach ($posts as $post)
                    <option value="{{ $post->id }}" @selected(old('recommended_post_id', $question->recommended_post_id ?? '') == $post->id)>{{ $post->title }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">💡 {{ __('Glossar- / Wissensbegriff') }}</label>
            <select name="knowledge_slug" class="mt-1.5 block w-full rounded-xl border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('— Aus Säule ableiten —') }}</option>
                @foreach ($glossaryTerms as $slug => $term)
                    <option value="{{ $slug }}" @selected(old('knowledge_slug', $question->knowledge_slug ?? '') == $slug)>{{ $term }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('admin.audit_questions.options') }}</label>
    <input name="options" value="{{ old('options', isset($question) && is_array($question->options) ? implode(', ', $question->options) : '') }}" placeholder="{{ __('Option A, Option B, Option C') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    <p class="mt-1 text-xs text-slate-500">{{ __('admin.audit_questions.options_help') }}</p>
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('admin.audit_questions.depends_on') }}</label>
        <select name="depends_on_question_id" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">{{ __('admin.audit_questions.no_dependency') }}</option>
            @foreach (($template->questions ?? $templateQuestions ?? []) as $q)
                <option value="{{ $q->id }}" @selected(old('depends_on_question_id', $question->depends_on_question_id ?? '') == $q->id)>{{ Str::limit($q->question, 60) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('admin.audit_questions.depends_on_answer') }}</label>
        <input name="depends_on_answer" value="{{ old('depends_on_answer', $question->depends_on_answer ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
</div>
