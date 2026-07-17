<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Routing\Controller;

class SitemapController extends Controller
{
    public function __invoke()
    {
        $urls = [];

        $urls[] = [
            'loc' => url('/'),
            'lastmod' => now()->toIso8601String(),
            'changefreq' => 'weekly',
            'priority' => '1.0',
        ];

        $pages = Page::where('is_active', true)->with('translations')->get();
        foreach ($pages as $page) {
            foreach ($page->translations as $translation) {
                $urls[] = [
                    'loc' => route('page.show', $translation->slug),
                    'lastmod' => $page->updated_at->toIso8601String(),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ];
            }
        }

        return response()->view('sitemap.index', compact('urls'))->header('Content-Type', 'text/xml');
    }
}
