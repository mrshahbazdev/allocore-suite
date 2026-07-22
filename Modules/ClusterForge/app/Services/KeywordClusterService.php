<?php

namespace Modules\ClusterForge\Services;

class KeywordClusterService
{
    public function cluster(array $keywords, string $algorithm = 'terms', int $minClusterSize = 2, float $similarityThreshold = 0.65): array
    {
        $keywords = array_map('trim', array_unique(array_filter($keywords)));

        if (count($keywords) < 2) {
            return [__('Unclassified') => array_values($keywords)];
        }

        return match ($algorithm) {
            'similarity' => $this->similarityCluster($keywords, $minClusterSize, $similarityThreshold),
            'terms' => $this->termCluster($keywords, $minClusterSize),
            default => $this->termCluster($keywords, $minClusterSize),
        };
    }

    protected function termCluster(array $keywords, int $minClusterSize): array
    {
        $terms = [];
        foreach ($keywords as $keyword) {
            foreach ($this->extractTerms($keyword) as $word) {
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

        return $this->addUnclassified($result, $keywords, $assigned);
    }

    protected function similarityCluster(array $keywords, int $minClusterSize, float $threshold): array
    {
        $count = count($keywords);
        $parents = range(0, $count - 1);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if ($this->similarity($keywords[$i], $keywords[$j]) >= $threshold) {
                    $rootI = $this->find($parents, $i);
                    $rootJ = $this->find($parents, $j);
                    if ($rootI !== $rootJ) {
                        $parents[$rootJ] = $rootI;
                    }
                }
            }
        }

        $groups = [];
        for ($i = 0; $i < $count; $i++) {
            $root = $this->find($parents, $i);
            $groups[$root][] = $keywords[$i];
        }

        $result = [];
        $assigned = [];
        foreach ($groups as $group) {
            $unique = array_values(array_unique($group));
            if (count($unique) >= $minClusterSize) {
                $label = $this->label($this->bestTerm($unique));
                $result[$label] = $unique;
                $assigned = array_merge($assigned, $unique);
            } else {
                $assigned = array_merge($assigned, $unique);
            }
        }

        return $this->addUnclassified($result, $keywords, $assigned);
    }

    protected function find(array &$parents, int $x): int
    {
        $root = $x;
        while ($parents[$root] !== $root) {
            $root = $parents[$root];
        }

        while ($parents[$x] !== $root) {
            $next = $parents[$x];
            $parents[$x] = $root;
            $x = $next;
        }

        return $root;
    }

    protected function addUnclassified(array $result, array $keywords, array $assigned): array
    {
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

    protected function bestTerm(array $keywords): string
    {
        $termCounts = [];
        foreach ($keywords as $keyword) {
            foreach ($this->extractTerms($keyword) as $term) {
                $termCounts[$term] = ($termCounts[$term] ?? 0) + 1;
            }
        }
        if (empty($termCounts)) {
            return 'Mixed';
        }
        arsort($termCounts);

        return (string) array_key_first($termCounts);
    }

    protected function label(string $term): string
    {
        return ucwords($term).' '.__('cluster');
    }

    protected function similarity(string $a, string $b): float
    {
        $a = strtolower(trim($a));
        $b = strtolower(trim($b));
        if ($a === $b) {
            return 1.0;
        }

        $maxLen = max(strlen($a), strlen($b));
        if ($maxLen === 0) {
            return 0.0;
        }

        similar_text($a, $b, $percent);

        return $percent / 100;
    }
}
