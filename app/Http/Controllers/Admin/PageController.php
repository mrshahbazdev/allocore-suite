<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::ordered()->with('translations')->get();

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $locales = config('app.available_locales', ['en']);

        $rules = [
            'slug' => 'required|string|max:255|unique:pages,slug',
            'is_published' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];

        foreach ($locales as $locale) {
            $rules["translations.{$locale}.slug"] = [
                'required', 'string', 'max:255',
                Rule::unique('page_translations', 'slug')->where('locale', $locale),
            ];
            $rules["translations.{$locale}.title"] = 'required|string|max:255';
            $rules["translations.{$locale}.meta_title"] = 'nullable|string|max:255';
            $rules["translations.{$locale}.meta_description"] = 'nullable|string|max:1000';
            $rules["translations.{$locale}.meta_keywords"] = 'nullable|string|max:255';
            $rules["translations.{$locale}.og_title"] = 'nullable|string|max:255';
            $rules["translations.{$locale}.og_description"] = 'nullable|string|max:1000';
            $rules["translations.{$locale}.og_image"] = 'nullable|string|max:1000';
            $rules["translations.{$locale}.body"] = 'nullable|string|max:50000';
        }

        $validated = $request->validate($rules);

        $page = Page::create([
            'slug' => $validated['slug'],
            'is_published' => $request->boolean('is_published'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        foreach ($locales as $locale) {
            $page->translations()->create([
                'locale' => $locale,
                ...$validated['translations'][$locale],
            ]);
        }

        return redirect()->route('admin.pages.index')->with('success', __('Page created.'));
    }

    public function edit(Page $page)
    {
        $page->load('translations');

        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $locales = config('app.available_locales', ['en']);

        $rules = [
            'slug' => ['required', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($page->id)],
            'is_published' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];

        foreach ($locales as $locale) {
            $translation = $page->translations()->where('locale', $locale)->first();

            $rules["translations.{$locale}.slug"] = [
                'required', 'string', 'max:255',
                Rule::unique('page_translations', 'slug')
                    ->where('locale', $locale)
                    ->ignore($translation?->id),
            ];
            $rules["translations.{$locale}.title"] = 'required|string|max:255';
            $rules["translations.{$locale}.meta_title"] = 'nullable|string|max:255';
            $rules["translations.{$locale}.meta_description"] = 'nullable|string|max:1000';
            $rules["translations.{$locale}.meta_keywords"] = 'nullable|string|max:255';
            $rules["translations.{$locale}.og_title"] = 'nullable|string|max:255';
            $rules["translations.{$locale}.og_description"] = 'nullable|string|max:1000';
            $rules["translations.{$locale}.og_image"] = 'nullable|string|max:1000';
            $rules["translations.{$locale}.body"] = 'nullable|string|max:50000';
        }

        $validated = $request->validate($rules);

        $page->update([
            'slug' => $validated['slug'],
            'is_published' => $request->boolean('is_published'),
            'sort_order' => $validated['sort_order'] ?? $page->sort_order,
        ]);

        foreach ($locales as $locale) {
            $page->translations()->updateOrCreate(
                ['locale' => $locale],
                $validated['translations'][$locale]
            );
        }

        return redirect()->route('admin.pages.index')->with('success', __('Page updated.'));
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', __('Page deleted.'));
    }

    public function reorder(Request $request)
    {
        $order = $request->input('order');

        if (is_string($order)) {
            $order = json_decode($order, true);
        }

        $validated = $request->merge(['order' => $order])->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:pages,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            Page::where('id', $id)->update(['sort_order' => $index]);
        }

        return redirect()->route('admin.pages.index')->with('success', __('Page order updated.'));
    }
}
