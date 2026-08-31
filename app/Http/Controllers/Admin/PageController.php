<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
        $locales = config('app.available_locales', ['en', 'de']);
        $defaultLocale = config('app.locale', 'de');

        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:pages,slug',
            'type' => 'required|in:page,help',
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'translations' => 'required|array',
        ]);

        $primaryLocale = $defaultLocale;
        if (empty($request->input("translations.{$primaryLocale}.title"))) {
            $primaryLocale = 'en';
            if (empty($request->input("translations.{$primaryLocale}.title"))) {
                foreach ($locales as $l) {
                    if (! empty($request->input("translations.{$l}.title"))) {
                        $primaryLocale = $l;
                        break;
                    }
                }
            }
        }

        $primaryData = $request->input("translations.{$primaryLocale}") ?? [];
        if (empty($primaryData['title'])) {
            return back()->withInput()->withErrors(['slug' => __('Please provide at least a title for the page.')]);
        }

        $page = Page::create([
            'slug' => Str::slug($validated['slug']),
            'type' => $validated['type'],
            'is_published' => $request->boolean('is_published'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        foreach ($locales as $locale) {
            $trans = $request->input("translations.{$locale}") ?? [];
            $title = ! empty($trans['title']) ? $trans['title'] : $primaryData['title'];
            $slug = ! empty($trans['slug']) ? Str::slug($trans['slug']) : Str::slug($validated['slug']);
            $body = ! empty($trans['body']) ? $trans['body'] : ($primaryData['body'] ?? null);

            $page->translations()->create([
                'locale' => $locale,
                'slug' => $slug,
                'title' => $title,
                'body' => $body,
                'meta_title' => $trans['meta_title'] ?? ($primaryData['meta_title'] ?? null),
                'meta_description' => $trans['meta_description'] ?? ($primaryData['meta_description'] ?? null),
                'meta_keywords' => $trans['meta_keywords'] ?? ($primaryData['meta_keywords'] ?? null),
                'og_title' => $trans['og_title'] ?? ($primaryData['og_title'] ?? null),
                'og_description' => $trans['og_description'] ?? ($primaryData['og_description'] ?? null),
                'og_image' => $trans['og_image'] ?? ($primaryData['og_image'] ?? null),
                'blocks' => $trans['blocks'] ?? ($primaryData['blocks'] ?? null),
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
        $locales = config('app.available_locales', ['en', 'de']);
        $defaultLocale = config('app.locale', 'de');

        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($page->id)],
            'type' => 'required|in:page,help',
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'translations' => 'required|array',
        ]);

        $primaryLocale = $defaultLocale;
        if (empty($request->input("translations.{$primaryLocale}.title"))) {
            $primaryLocale = 'en';
            if (empty($request->input("translations.{$primaryLocale}.title"))) {
                foreach ($locales as $l) {
                    if (! empty($request->input("translations.{$l}.title"))) {
                        $primaryLocale = $l;
                        break;
                    }
                }
            }
        }

        $primaryData = $request->input("translations.{$primaryLocale}") ?? [];
        if (empty($primaryData['title'])) {
            $existingFirst = $page->translations()->first();
            $primaryData['title'] = $existingFirst?->title ?: $validated['slug'];
        }

        $page->update([
            'slug' => Str::slug($validated['slug']),
            'type' => $validated['type'],
            'is_published' => $request->boolean('is_published'),
            'sort_order' => $validated['sort_order'] ?? $page->sort_order,
        ]);

        foreach ($locales as $locale) {
            $trans = $request->input("translations.{$locale}") ?? [];
            $title = ! empty($trans['title']) ? $trans['title'] : $primaryData['title'];
            $slug = ! empty($trans['slug']) ? Str::slug($trans['slug']) : Str::slug($validated['slug']);
            $body = isset($trans['body']) && $trans['body'] !== '' ? $trans['body'] : ($primaryData['body'] ?? null);

            $page->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'slug' => $slug,
                    'title' => $title,
                    'body' => $body,
                    'meta_title' => $trans['meta_title'] ?? ($primaryData['meta_title'] ?? null),
                    'meta_description' => $trans['meta_description'] ?? ($primaryData['meta_description'] ?? null),
                    'meta_keywords' => $trans['meta_keywords'] ?? ($primaryData['meta_keywords'] ?? null),
                    'og_title' => $trans['og_title'] ?? ($primaryData['og_title'] ?? null),
                    'og_description' => $trans['og_description'] ?? ($primaryData['og_description'] ?? null),
                    'og_image' => $trans['og_image'] ?? ($primaryData['og_image'] ?? null),
                    'blocks' => $trans['blocks'] ?? ($primaryData['blocks'] ?? null),
                ]
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
