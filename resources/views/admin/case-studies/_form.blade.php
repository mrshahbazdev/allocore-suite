@csrf

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Title') }}</label>
    <input name="title" value="{{ old('title', $caseStudy->title ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Slug') }}</label>
        <input name="slug" value="{{ old('slug', $caseStudy->slug ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="auto-generated">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Company') }}</label>
        <input name="company" value="{{ old('company', $caseStudy->company ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Industry') }}</label>
        <input name="industry" value="{{ old('industry', $caseStudy->industry ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Image URL') }}</label>
        <input name="image" value="{{ old('image', $caseStudy->image ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://...">
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Challenge') }}</label>
    <textarea name="challenge" rows="3" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('challenge', $caseStudy->challenge ?? '') }}</textarea>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Solution') }}</label>
    <textarea name="solution" rows="3" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('solution', $caseStudy->solution ?? '') }}</textarea>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Result') }}</label>
    <textarea name="result" rows="3" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('result', $caseStudy->result ?? '') }}</textarea>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Metrics') }}</label>
    <textarea name="metrics" rows="3" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Umsatz: +24%\nZeitersparnis: 12h/Woche">{{ old('metrics', isset($caseStudy) ? collect($caseStudy->metrics ?? [])->map(fn ($v, $k) => "$k: $v")->implode("\n") : '') }}</textarea>
    <p class="mt-1 text-xs text-slate-500">{{ __('One metric per line: Label: Value') }}</p>
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Sort order') }}</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $caseStudy->sort_order ?? 0) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div class="flex items-end gap-2">
        <input id="is_published" name="is_published" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_published', $caseStudy->is_published ?? false))>
        <label for="is_published" class="text-sm font-medium text-slate-700">{{ __('Published') }}</label>
    </div>
</div>
