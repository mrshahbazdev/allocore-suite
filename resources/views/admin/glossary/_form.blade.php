@csrf

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Term') }}</label>
    <input name="term" value="{{ old('term', $glossary->term ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Slug') }}</label>
        <input name="slug" value="{{ old('slug', $glossary->slug ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="auto-generated">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Category') }}</label>
        <input name="category" value="{{ old('category', $glossary->category ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. Finance, Strategy">
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Definition') }}</label>
    <textarea name="definition" rows="5" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('definition', $glossary->definition ?? '') }}</textarea>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">{{ __('Related modules') }}</label>
    <input name="related_modules" value="{{ old('related_modules', isset($glossary) ? collect($glossary->related_modules ?? [])->implode(', ') : '') }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="financial-platform, sweet-spot">
    <p class="mt-1 text-xs text-slate-500">{{ __('Comma-separated module keys') }}</p>
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Sort order') }}</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $glossary->sort_order ?? 0) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div class="flex items-end gap-2">
        <input id="is_published" name="is_published" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_published', $glossary->is_published ?? false))>
        <label for="is_published" class="text-sm font-medium text-slate-700">{{ __('Published') }}</label>
    </div>
</div>
