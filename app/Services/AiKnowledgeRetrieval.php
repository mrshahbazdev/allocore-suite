<?php

namespace App\Services;

use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\KnowledgeManager\Models\Project;
use Modules\LoopEngine\Models\Process;
use Modules\SopBuilder\Models\Sop;

class AiKnowledgeRetrieval
{
    public function search(User $user, string $query, int $limit = 5): Collection
    {
        $words = $this->keywords($query);

        if ($words->isEmpty()) {
            return collect();
        }

        $results = collect();
        $results = $results->merge($this->searchPages($user, $words));
        $results = $results->merge($this->searchKnowledgeProjects($user, $words));
        $results = $results->merge($this->searchLoopEngineProcesses($user, $words));
        $results = $results->merge($this->searchSops($user, $words));

        return $results
            ->sortByDesc('score')
            ->slice(0, $limit)
            ->values();
    }

    public function contextFor(User $user, string $query, int $limit = 5): string
    {
        $results = $this->search($user, $query, $limit);

        if ($results->isEmpty()) {
            return '';
        }

        $parts = ["Retrieved knowledge sources:\n"];

        foreach ($results as $index => $item) {
            $num = $index + 1;
            $parts[] = "[{$num}] {$item['title']} ({$item['source']})\n{$item['excerpt']}\n";
        }

        $parts[] = "\nAnswer using the sources above. Cite sources with [1], [2], etc. when possible.";

        return implode("\n", $parts);
    }

    protected function keywords(string $query): Collection
    {
        $stop = ['the', 'a', 'an', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'shall', 'to', 'of', 'in', 'for', 'on', 'with', 'at', 'from', 'as', 'and', 'or', 'but', 'if', 'then', 'than', 'what', 'how', 'where', 'when', 'who', 'why', 'which', 'this', 'that', 'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them', 'my', 'your', 'his', 'her', 'its', 'our', 'their'];

        return collect(explode(' ', strtolower($query)))
            ->map(fn ($w) => preg_replace('/[^a-z0-9]/', '', $w))
            ->filter(fn ($w) => strlen($w) > 2 && ! in_array($w, $stop, true))
            ->unique()
            ->values();
    }

    protected function scoreText(string $text, Collection $words, float $titleWeight = 1): float
    {
        $text = strtolower(strip_tags($text));
        $score = 0;

        foreach ($words as $word) {
            $score += substr_count($text, $word) * $titleWeight;
        }

        return $score;
    }

    protected function searchPages(User $user, Collection $words): Collection
    {
        $locale = app()->getLocale();

        return Page::with(['translations' => fn ($q) => $q->where('locale', $locale)])
            ->published()
            ->where('type', 'page')
            ->get()
            ->map(function (Page $page) use ($words) {
                $translation = $page->translations->first();

                if (! $translation) {
                    return null;
                }

                $text = ($translation->title ?? '').' '.($translation->body ?? '');
                $score = $this->scoreText($text, $words, 2);

                if ($score <= 0) {
                    return null;
                }

                return [
                    'title' => $translation->title,
                    'source' => __('Wiki'),
                    'url' => route('page.show', $translation->slug),
                    'excerpt' => Str::limit(strip_tags($translation->body ?? ''), 200),
                    'score' => $score,
                ];
            })
            ->filter()
            ->values();
    }

    protected function searchKnowledgeProjects(User $user, Collection $words): Collection
    {
        if (! class_exists(Project::class) || ! $user->hasModule('knowledge-manager')) {
            return collect();
        }

        return Project::with(['answers', 'assets'])
            ->get()
            ->map(function ($project) use ($words) {
                $text = ($project->name ?? '').' '.($project->description ?? '');

                foreach ($project->answers as $answer) {
                    $text .= ' '.($answer->answer ?? '');
                }

                foreach ($project->assets as $asset) {
                    $text .= ' '.($asset->name ?? '').' '.($asset->description ?? '');
                }

                $score = $this->scoreText($text, $words, 3);

                if ($score <= 0) {
                    return null;
                }

                return [
                    'title' => $project->name,
                    'source' => __('Knowledge Project'),
                    'url' => route('knowledgemanager.projects.show', $project),
                    'excerpt' => Str::limit(strip_tags($project->description ?? ''), 200),
                    'score' => $score,
                ];
            })
            ->filter()
            ->values();
    }

    protected function searchLoopEngineProcesses(User $user, Collection $words): Collection
    {
        if (! class_exists(Process::class) || ! $user->hasModule('loop-engine')) {
            return collect();
        }

        $locale = app()->getLocale();
        $nameCol = $locale === 'de' ? 'name_de' : 'name_en';
        $descCol = $locale === 'de' ? 'description_de' : 'description_en';
        $questionCol = $locale === 'de' ? 'question_de' : 'question_en';
        $helpCol = $locale === 'de' ? 'help_text_de' : 'help_text_en';

        return Process::with('steps')
            ->get()
            ->map(function ($process) use ($words, $nameCol, $descCol, $questionCol, $helpCol) {
                $text = ($process->$nameCol ?? '').' '.($process->$descCol ?? '');

                foreach ($process->steps as $step) {
                    $text .= ' '.($step->$questionCol ?? '').' '.($step->$helpCol ?? '');
                }

                $score = $this->scoreText($text, $words, 3);

                if ($score <= 0) {
                    return null;
                }

                return [
                    'title' => $process->$nameCol,
                    'source' => __('SOP'),
                    'url' => route('loopengine.processes.show', $process),
                    'excerpt' => Str::limit(strip_tags($process->$descCol ?? ''), 200),
                    'score' => $score,
                ];
            })
            ->filter()
            ->values();
    }

    protected function searchSops(User $user, Collection $words): Collection
    {
        if (! class_exists(Sop::class) || ! $user->hasModule('sop-builder')) {
            return collect();
        }

        return Sop::with(['steps', 'checklist', 'quizzes'])
            ->get()
            ->map(function ($sop) use ($words) {
                $text = ($sop->title ?? '').' '.($sop->description ?? '').' '.($sop->why ?? '').' '.($sop->who ?? '').' '.($sop->when ?? '').' '.($sop->input ?? '').' '.($sop->output ?? '').' '.($sop->risks ?? '').' '.($sop->tools ?? '');

                foreach ($sop->steps as $step) {
                    $text .= ' '.($step->title ?? '').' '.($step->description ?? '');
                }

                foreach ($sop->checklist as $item) {
                    $text .= ' '.($item->text ?? '');
                }

                foreach ($sop->quizzes as $quiz) {
                    $text .= ' '.($quiz->question ?? '').' '.($quiz->explanation ?? '');
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
}
