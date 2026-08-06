<?php

namespace Modules\CustomerSuccess\Services;

use App\Models\User;
use App\Services\AiKnowledgeRetrieval;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\AuditIntelligence\Models\Finding;
use Modules\SopBuilder\Models\Sop;

class CustomerSuccessAssistant
{
    public function ask(User $user, string $question, ?string $moduleKey = null): array
    {
        $sources = $this->sources($user, $question);
        $context = $this->contextFor($sources);

        if ($apiKey = config('services.openai.key')) {
            try {
                $response = $this->callOpenAi($apiKey, $question, $context);

                return array_merge($this->parseResponse($response), ['sources' => $sources->all()]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return array_merge($this->localResponse($sources, $question), ['sources' => $sources->all()]);
    }

    public function sources(User $user, string $question)
    {
        $results = collect();

        $results = $results->merge(app(AiKnowledgeRetrieval::class)->search($user, $question, 5));
        $results = $results->merge($this->searchFindings($user, $question));
        $results = $results->merge($this->searchSops($user, $question));

        return $results->sortByDesc('score')->slice(0, 5)->values();
    }

    protected function contextFor($sources): string
    {
        if ($sources->isEmpty()) {
            return 'No specific sources retrieved.';
        }

        $parts = ["Retrieved sources:\n"];

        foreach ($sources as $index => $source) {
            $num = $index + 1;
            $parts[] = "[{$num}] {$source['title']} ({$source['source']})\n{$source['excerpt']}\n";
        }

        return implode("\n", $parts);
    }

    protected function searchFindings(User $user, string $question)
    {
        if (! class_exists(Finding::class) || ! $user->hasModule('audit-intelligence')) {
            return collect();
        }

        $words = $this->keywords($question);

        return Finding::with('recommendations')
            ->get()
            ->map(function ($finding) use ($words) {
                $text = collect([$finding->title, $finding->description, $finding->risk_level, $finding->priority, $finding->status])->implode(' ');
                $score = $this->scoreText($text, $words, 3);

                if ($finding->recommendations) {
                    foreach ($finding->recommendations as $recommendation) {
                        $score += $this->scoreText($recommendation->issue.' '.$recommendation->solution, $words, 2);
                    }
                }

                if ($score <= 0) {
                    return null;
                }

                return [
                    'title' => $finding->title,
                    'source' => __('Audit Finding'),
                    'url' => route('auditintelligence.findings.show', $finding),
                    'excerpt' => Str::limit(strip_tags($finding->description ?? ''), 200),
                    'score' => $score,
                ];
            })
            ->filter()
            ->values();
    }

    protected function searchSops(User $user, string $question)
    {
        if (! class_exists(Sop::class) || ! $user->hasModule('sop-builder')) {
            return collect();
        }

        $words = $this->keywords($question);

        return Sop::with('steps')
            ->get()
            ->map(function ($sop) use ($words) {
                $text = ($sop->title ?? '').' '.($sop->description ?? '').' '.($sop->why ?? '').' '.($sop->who ?? '').' '.($sop->when ?? '').' '.($sop->input ?? '').' '.($sop->output ?? '').' '.($sop->risks ?? '').' '.($sop->tools ?? '');

                foreach ($sop->steps as $step) {
                    $text .= ' '.($step->title ?? '').' '.($step->description ?? '');
                }

                $score = $this->scoreText($text, $words, 4);

                if ($score <= 0) {
                    return null;
                }

                return [
                    'title' => $sop->title,
                    'source' => __('SOP'),
                    'url' => route('sopbuilder.sops.show', $sop),
                    'excerpt' => Str::limit(strip_tags($sop->why ?? $sop->description ?? ''), 200),
                    'score' => $score,
                ];
            })
            ->filter()
            ->values();
    }

    protected function keywords(string $query)
    {
        $stop = ['the', 'a', 'an', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'shall', 'to', 'of', 'in', 'for', 'on', 'with', 'at', 'from', 'as', 'and', 'or', 'but', 'if', 'then', 'than', 'what', 'how', 'where', 'when', 'who', 'why', 'which', 'this', 'that', 'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them', 'my', 'your', 'his', 'her', 'its', 'our', 'their'];

        return collect(explode(' ', strtolower($query)))
            ->map(fn ($w) => preg_replace('/[^a-z0-9]/', '', $w))
            ->filter(fn ($w) => strlen($w) > 2 && ! in_array($w, $stop, true))
            ->unique()
            ->values();
    }

    protected function scoreText(string $text, $words, float $titleWeight = 1): float
    {
        $text = strtolower(strip_tags($text));
        $score = 0;

        foreach ($words as $word) {
            $score += substr_count($text, $word) * $titleWeight;
        }

        return $score;
    }

    protected function callOpenAi(string $apiKey, string $question, string $context): string
    {
        $prompt = $this->systemPrompt();

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => "Context:\n{$context}\n\nQuestion: {$question}\n\nProvide the structured response."],
                ],
                'max_tokens' => 1024,
            ]);

        return $response->json('choices.0.message.content')
            ?? 'I could not generate a response right now. Please try again.';
    }

    protected function systemPrompt(): string
    {
        return <<<'PROMPT'
You are the Allocore Customer Success Assistant. Answer only using the retrieved context when possible.
Provide a structured response in this exact format:

Problem: <short problem statement>
Root Cause: <why it happens>
Consequences: <what happens if not fixed>
Recommended Actions: <numbered or bulleted steps>
Priority: <low|medium|high|critical>
Estimated Cost: <short cost estimate or "Unknown">
Expected Benefit: <benefit of fixing it>
PROMPT;
    }

    protected function parseResponse(string $response): array
    {
        $fields = [
            'answer' => $response,
            'problem' => null,
            'root_cause' => null,
            'consequences' => null,
            'recommended_actions' => null,
            'priority' => null,
            'estimated_cost' => null,
            'expected_benefit' => null,
        ];

        $mapping = [
            'problem' => ['Problem:', 'The problem'],
            'root_cause' => ['Root Cause:', 'Root cause'],
            'consequences' => ['Consequences:', 'Impact'],
            'recommended_actions' => ['Recommended Actions:', 'Actions'],
            'priority' => ['Priority:', 'Priority'],
            'estimated_cost' => ['Estimated Cost:', 'Cost'],
            'expected_benefit' => ['Expected Benefit:', 'Benefit'],
        ];

        foreach ($mapping as $field => $labels) {
            foreach ($labels as $label) {
                if (preg_match('/'.preg_quote($label, '/').'\s*[:\-]?\s*(.*?)(?=\n[A-Z][^:]*[:\-]|\z)/s', $response, $matches)) {
                    $fields[$field] = trim($matches[1]);
                    break;
                }
            }
        }

        if (empty($fields['problem'])) {
            $fields['problem'] = Str::limit(strip_tags($response), 200);
        }

        return $fields;
    }

    protected function localResponse($sources, string $question): array
    {
        if ($sources->isEmpty()) {
            return [
                'answer' => __('I could not find any matching documentation, audit findings or SOPs. Try rephrasing your question.'),
                'problem' => __('No relevant source found.'),
                'root_cause' => null,
                'consequences' => null,
                'recommended_actions' => null,
                'priority' => null,
                'estimated_cost' => null,
                'expected_benefit' => null,
            ];
        }

        $top = $sources->first();
        $more = $sources->count() - 1;

        return [
            'answer' => $top['excerpt'].($more > 0 ? ' '.sprintf(__('+%d more sources found.'), $more) : '').' '.__('Source:').' '.$top['url'],
            'problem' => $top['title'],
            'root_cause' => $top['excerpt'],
            'consequences' => __('If this is not addressed, the finding may remain unresolved or the process may not be followed.'),
            'recommended_actions' => __('Review the source and follow the documented steps.'),
            'priority' => __('medium'),
            'estimated_cost' => __('Unknown'),
            'expected_benefit' => __('Clarity and faster resolution of the issue.'),
        ];
    }
}
