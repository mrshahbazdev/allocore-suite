<?php

namespace Modules\ClusterForge\Services;

class KeywordClusterService
{
    /**
     * Cluster a list of keywords by common terms.
     */
    public function cluster(array $keywords, int $minClusterSize = 2): array
    {
        $keywords = array_map('trim', array_unique(array_filter($keywords)));

        $terms = [];
        foreach ($keywords as $keyword) {
            $words = $this->extractTerms($keyword);
            foreach ($words as $word) {
                $terms[$word][] = $keyword;
            }
        }

        $clusters = [];
        foreach ($terms as $term => $matches) {
            if (count($matches) >= $minClusterSize) {
                $clusters[$term] = array_values(array_unique($matches));
            }
        }

        $assigned = [];
        $result = [];
        foreach ($clusters as $term => $matches) {
            $unassigned = array_values(array_filter($matches, fn ($k) => ! in_array($k, $assigned, true)));
            if (count($unassigned) >= $minClusterSize) {
                $result[$this->label($term)] = $unassigned;
                $assigned = array_merge($assigned, $unassigned);
            }
        }

        $remaining = array_values(array_filter($keywords, fn ($k) => ! in_array($k, $assigned, true)));
        if (! empty($remaining)) {
            $result[__('Unclassified')] = $remaining;
        }

        return $result;
    }

    protected function extractTerms(string $keyword): array
    {
        $normalized = strtolower(preg_replace('/[^\w\s]/u', ' ', $keyword) ?? $keyword);
        $words = array_unique(array_filter(preg_split('/\s+/', $normalized)));

        return array_values(array_filter($words, fn ($w) => strlen($w) >= 3));
    }

    protected function label(string $term): string
    {
        return ucwords($term).' '.__('cluster');
    }
}
