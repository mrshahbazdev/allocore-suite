<?php

namespace App\Services;

use App\Models\GlossaryTerm;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GlossaryService
{
    public function linkTerms(?string $text, int $limit = -1): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $terms = $this->publishedTerms();

        if ($terms->isEmpty()) {
            return e($text);
        }

        $patterns = $terms->sortByDesc(fn ($term) => mb_strlen($term['term']))
            ->map(fn ($term) => preg_quote($term['term'], '/'))
            ->all();

        $pattern = '/\b('.implode('|', $patterns).')\b/iu';
        $termMap = $terms->keyBy(fn ($term) => mb_strtolower($term['term']));
        $replaced = 0;

        return preg_replace_callback($pattern, function ($matches) use ($termMap, $limit, &$replaced) {
            if ($limit !== -1 && $replaced >= $limit) {
                return $matches[0];
            }

            $term = $termMap->get(mb_strtolower($matches[1]));

            if (! $term) {
                return $matches[0];
            }

            $replaced++;
            $url = route('glossary.show', $term['slug']);
            $title = e(Str::limit($term['simple_definition'] ?: $term['definition'], 160));

            return '<a href="'.$url.'" class="glossary-link" title="'.$title.'">'.$matches[0].'</a>';
        }, e($text));
    }

    public function linkHtml(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $terms = $this->publishedTerms();

        if ($terms->isEmpty()) {
            return $html;
        }

        $inLink = false;
        $inSkip = false;
        $self = $this;

        return preg_replace_callback('/<[^>]+>|[^<]+/s', function ($matches) use (&$inLink, &$inSkip, $self) {
            $chunk = $matches[0];

            if (str_starts_with($chunk, '<')) {
                $tag = strtolower(trim($chunk, '<'));
                $tagName = strtok($tag, " \t\r\n/>");

                if (in_array($tagName, ['script', 'style', 'textarea', 'title', 'head', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'button', 'select', 'option', 'svg', 'nav'], true) || str_contains($tag, 'no-glossary')) {
                    $inSkip = ! str_starts_with($chunk, '</');
                }

                if ($tagName === 'a') {
                    $inLink = ! str_starts_with($chunk, '</');
                }

                return $chunk;
            }

            if ($inSkip || $inLink || trim($chunk) === '') {
                return $chunk;
            }

            $text = htmlspecialchars_decode($chunk, ENT_QUOTES | ENT_HTML5);

            return $self->linkTerms($text);
        }, $html) ?? $html;
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
            .$terms->map(fn ($term) => $term['term'].': '.($term['simple_definition'] ?: $term['definition']))->implode("\n");
    }

    private function publishedTerms()
    {
        $terms = Cache::remember('glossary.published_terms', 300, function () {
            return GlossaryTerm::published()->orderBy('term')->get()->map(fn (GlossaryTerm $term) => $term->toArray())->all();
        });

        return collect($terms);
    }
}
