@php($isEdit = isset($page))
@php($locales = config('app.available_locales', ['en']))

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <label for="slug" class="block text-sm font-medium text-slate-700">{{ __('cms.base_slug') }}</label>
            <input id="slug" name="slug" type="text" value="{{ old('slug', $page?->slug) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        </div>

        <div>
            <label for="type" class="block text-sm font-medium text-slate-700">{{ __('cms.page_type') }}</label>
            <select id="type" name="type" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="page" {{ old('type', $page?->type) === 'page' ? 'selected' : '' }}>{{ __('cms.type_page') }}</option>
                <option value="help" {{ old('type', $page?->type) === 'help' ? 'selected' : '' }}>{{ __('cms.type_help') }}</option>
            </select>
        </div>

        <div class="flex items-end gap-4">
            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $page?->is_published) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                {{ __('cms.published') }}
            </label>

            <div class="flex-1">
                <label for="sort_order" class="block text-sm font-medium text-slate-700">{{ __('cms.order') }}</label>
                <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $page?->sort_order ?? 0) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    <div class="mt-8 space-y-4">
        @foreach ($locales as $locale)
            @php($translation = $page?->translations?->firstWhere('locale', $locale))
            <details class="rounded-xl border border-slate-200 bg-white" {{ $loop->first ? 'open' : '' }}>
                <summary class="cursor-pointer px-6 py-4 text-sm font-semibold text-slate-900">
                    {{ config('app.locale_names.'.$locale, strtoupper($locale)) }}
                </summary>
                <div class="border-t border-slate-200 px-6 py-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('cms.url_slug') }}</label>
                            <input name="translations[{{ $locale }}][slug]" type="text" value="{{ old('translations.'.$locale.'.slug', $translation?->slug) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('cms.title') }}</label>
                            <input name="translations[{{ $locale }}][title]" type="text" value="{{ old('translations.'.$locale.'.title', $translation?->title) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('cms.meta_title') }}</label>
                            <input name="translations[{{ $locale }}][meta_title]" type="text" value="{{ old('translations.'.$locale.'.meta_title', $translation?->meta_title) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('cms.meta_keywords') }}</label>
                            <input name="translations[{{ $locale }}][meta_keywords]" type="text" value="{{ old('translations.'.$locale.'.meta_keywords', $translation?->meta_keywords) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">{{ __('cms.meta_description') }}</label>
                            <textarea name="translations[{{ $locale }}][meta_description]" rows="2" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('translations.'.$locale.'.meta_description', $translation?->meta_description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('cms.og_title') }}</label>
                            <input name="translations[{{ $locale }}][og_title]" type="text" value="{{ old('translations.'.$locale.'.og_title', $translation?->og_title) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">{{ __('cms.og_image') }}</label>
                            <input name="translations[{{ $locale }}][og_image]" type="text" value="{{ old('translations.'.$locale.'.og_image', $translation?->og_image) }}" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">{{ __('cms.og_description') }}</label>
                            <textarea name="translations[{{ $locale }}][og_description]" rows="2" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('translations.'.$locale.'.og_description', $translation?->og_description) }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">{{ __('cms.body_html') }}</label>
                            <textarea name="translations[{{ $locale }}][body]" rows="12" class="mt-2 block w-full rounded-lg border-slate-300 px-3 py-2 font-mono text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('translations.'.$locale.'.body', $translation?->body) }}</textarea>
                        </div>
                    </div>
                </div>
            </details>
        @endforeach
    </div>

    <div class="mt-8 flex items-center justify-end">
        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
            {{ $isEdit ? __('cms.update_page') : __('cms.create_page') }}
        </button>
    </div>
</form>
