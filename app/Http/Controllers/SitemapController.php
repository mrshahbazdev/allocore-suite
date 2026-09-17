<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\CaseStudy;
use App\Models\GlossaryTerm;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Routing\Controller;

class SitemapController extends Controller
{
    public function __invoke()
    {
        $urls = [];

        // 1. Core High-Priority Pages
        $staticRoutes = [
            ['url' => url('/'), 'priority' => '1.0', 'freq' => 'weekly'],
            ['url' => route('blog.index'), 'priority' => '0.9', 'freq' => 'daily'],
            ['url' => route('case-studies.index'), 'priority' => '0.8', 'freq' => 'weekly'],
            ['url' => route('glossary.index'), 'priority' => '0.8', 'freq' => 'weekly'],
            ['url' => route('audit-example.index'), 'priority' => '0.8', 'freq' => 'monthly'],
            ['url' => route('roi-calculator.index'), 'priority' => '0.8', 'freq' => 'monthly'],
            ['url' => route('faq.index'), 'priority' => '0.7', 'freq' => 'monthly'],
        ];

        foreach ($staticRoutes as $r) {
            $urls[] = [
                'loc' => $r['url'],
                'lastmod' => now()->toIso8601String(),
                'changefreq' => $r['freq'],
                'priority' => $r['priority'],
            ];
        }

        // 2. Published Blog Posts
        $posts = Post::where('is_published', true)->latest('updated_at')->get();
        foreach ($posts as $post) {
            $urls[] = [
                'loc' => route('blog.show', $post->slug),
                'lastmod' => ($post->updated_at ?? now())->toIso8601String(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ];
        }

        // 3. Blog Categories
        $categories = BlogCategory::where('is_active', true)->get();
        foreach ($categories as $cat) {
            $urls[] = [
                'loc' => route('blog.category', $cat->slug),
                'lastmod' => ($cat->updated_at ?? now())->toIso8601String(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        // 4. Blog Tags
        $tags = BlogTag::get();
        foreach ($tags as $tag) {
            $urls[] = [
                'loc' => route('blog.tag', $tag->slug),
                'lastmod' => ($tag->updated_at ?? now())->toIso8601String(),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }

        // 5. Case Studies
        $caseStudies = CaseStudy::where('is_published', true)->get();
        foreach ($caseStudies as $cs) {
            $urls[] = [
                'loc' => route('case-studies.show', $cs->slug),
                'lastmod' => ($cs->updated_at ?? now())->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
        }

        // 6. Glossary Terms
        $terms = GlossaryTerm::published()->get();
        foreach ($terms as $term) {
            $urls[] = [
                'loc' => route('glossary.show', $term->slug),
                'lastmod' => ($term->updated_at ?? now())->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        // 7. CMS Pages
        $pages = Page::published()->with('translations')->get();
        foreach ($pages as $page) {
            foreach ($page->translations as $translation) {
                $urls[] = [
                    'loc' => route('page.show', $translation->slug),
                    'lastmod' => ($page->updated_at ?? now())->toIso8601String(),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ];
            }
        }

        return response()->view('sitemap.index', compact('urls'))->header('Content-Type', 'text/xml');
    }
}
