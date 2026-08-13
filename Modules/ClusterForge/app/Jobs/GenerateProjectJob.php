<?php

namespace Modules\ClusterForge\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\ClusterForge\Models\Project;
use Modules\ClusterForge\Services\KeywordClusterGenerator;
use Throwable;

class GenerateProjectJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200;

    public int $tries = 3;

    public array $backoff = [30, 60, 120];

    public int $uniqueFor = 1200;

    public function __construct(public int $projectId) {}

    public function uniqueId(): string
    {
        return 'generate-project-'.$this->projectId;
    }

    public function handle(KeywordClusterGenerator $generator): void
    {
        $project = Project::find($this->projectId);
        if (! $project) {
            return;
        }

        try {
            $project->update(['status' => Project::STATUS_GENERATING_SUBTOPICS, 'error' => null]);
            $generator->generateSubtopics($project);

            $project->update(['status' => Project::STATUS_GENERATING_QUESTIONS]);
            foreach ($project->subtopics()->get() as $subtopic) {
                $generator->generateQuestionsForSubtopic($subtopic);
            }

            $project->update(['status' => Project::STATUS_GENERATING_ANSWERS]);
            foreach ($project->subtopics()->get() as $subtopic) {
                $generator->generateAnswersForSubtopic($subtopic);
            }

            $project->update(['status' => Project::STATUS_GENERATING_PAGES]);
            foreach ($project->subtopics()->get() as $subtopic) {
                $generator->generateClusterPage($subtopic);
            }
            $generator->generatePillarPage($project->fresh());

            $project->update(['status' => Project::STATUS_COMPLETED]);
        } catch (Throwable $e) {
            Log::error('GenerateProjectJob failed', [
                'project_id' => $this->projectId,
                'message' => $e->getMessage(),
            ]);

            $project->update([
                'status' => Project::STATUS_FAILED,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        $project = Project::find($this->projectId);
        if ($project) {
            $project->update([
                'status' => Project::STATUS_FAILED,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
