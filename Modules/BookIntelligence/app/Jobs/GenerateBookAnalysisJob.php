<?php

namespace Modules\BookIntelligence\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\BookIntelligence\Models\BookAnalysis;
use Modules\BookIntelligence\Services\BookAnalysisGenerator;
use Throwable;

class GenerateBookAnalysisJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 3;

    public array $backoff = [30, 90, 180];

    public int $uniqueFor = 600;

    public function __construct(public int $analysisId) {}

    public function uniqueId(): string
    {
        return 'generate-book-analysis-'.$this->analysisId;
    }

    public function handle(BookAnalysisGenerator $generator): void
    {
        $analysis = BookAnalysis::withoutGlobalScopes()->find($this->analysisId);

        if (! $analysis) {
            return;
        }

        try {
            $analysis->update([
                'status' => BookAnalysis::STATUS_GENERATING,
                'error' => null,
            ]);

            $generator->generate($analysis);
        } catch (Throwable $exception) {
            Log::warning('Book intelligence analysis attempt failed', [
                'analysis_id' => $analysis->id,
                'book_id' => $analysis->book_id,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        $analysis = BookAnalysis::withoutGlobalScopes()->find($this->analysisId);

        if ($analysis) {
            $this->markFailed($analysis, $exception);
        }
    }

    private function markFailed(BookAnalysis $analysis, Throwable $exception): void
    {
        Log::error('Book intelligence analysis failed', [
            'analysis_id' => $analysis->id,
            'book_id' => $analysis->book_id,
            'message' => $exception->getMessage(),
        ]);

        $analysis->update([
            'status' => BookAnalysis::STATUS_FAILED,
            'error' => Str::limit($exception->getMessage(), 2000),
        ]);
    }
}
