<?php

namespace App\Services;

use App\Models\GlossaryTerm;
use Illuminate\Support\Facades\Cache;

class GlossaryService
{
    public function linkTerms(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $terms = $this->publishedTerms();

        if ($terms->isEmpty()) {
            return e($text);
        }

        $escaped = e($text);

        return $terms->sortByDesc(fn ($term) => mb_strlen($term->term))
            ->reduce(function (string $carry, GlossaryTerm $term) {
                $pattern = '/\b'.preg_quote($term->term, '/').'\b/iu';
                $url = route('glossary.show', $term->slug);

                return preg_replace_callback($pattern, function ($matches) use ($url, $term) {
                    return '<a href="'.$url.'" class="border-b border-dotted border-indigo-500 text-indigo-600 hover:text-indigo-800" title="'.e($term->term).'">'.$matches[0].'</a>';
                }, $carry, 1);
            }, $escaped);
    }

    public function relatedForModule(?string $moduleKey, int $limit = 5)
    {
        if (! $moduleKey) {
            return collect();
        }

        return GlossaryTerm::published()
            ->where(function ($query) use ($moduleKey) {
                $query->whereJsonContains('related_modules', $moduleKey)
                    ->orWhereJsonContains('related_modules', [$moduleKey]);
            })
            ->orderBy('is_beginner_friendly', 'desc')
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    public function relatedForPillar(string $pillar, int $limit = 5)
    {
        $moduleMap = [
            'Revenue' => ['financial-platform', 'invoice-maker', 'sweet-spot'],
            'Profit' => ['cash-core', 'financial-platform', 'sweet-spot'],
            'Order' => ['plan-hive', 'time-butler', 'loop-engine', 'focus-matrix'],
            'Influence' => ['keyword-cluster', 'lead-quality'],
            'Legacy' => ['vision-flow', 'nur-du', 'org-matrix'],
        ];

        $keys = $moduleMap[$pillar] ?? [];

        if (empty($keys)) {
            return collect();
        }

        return GlossaryTerm::published()
            ->where(function ($query) use ($keys) {
                foreach ($keys as $key) {
                    $query->orWhereJsonContains('related_modules', $key)
                        ->orWhereJsonContains('related_modules', [$key]);
                }
            })
            ->orderBy('is_beginner_friendly', 'desc')
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    public function contextPrompt(): string
    {
        $terms = $this->publishedTerms()->take(100);

        if ($terms->isEmpty()) {
            return '';
        }

        return "Use the following Allocore glossary definitions when explaining business concepts to users:\n\n"
            .$terms->map(fn (GlossaryTerm $term) => $term->term.': '.($term->simple_definition ?: $term->definition))->implode("\n");
    }

    private function publishedTerms()
    {
        $terms = Cache::remember('glossary.published_terms', 300, function () {
            return GlossaryTerm::published()->orderBy('term')->get()->all();
        });

        return collect($terms);
    }
}
