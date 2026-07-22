<?php

namespace Modules\ClusterForge\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\ClusterForge\Models\KeywordCluster;
use Modules\ClusterForge\Services\KeywordClusterService;
use Throwable;

class ClusterKeywordsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public KeywordCluster $cluster) {}

    public function handle(KeywordClusterService $service): void
    {
        $this->cluster->update(['status' => 'processing']);

        $clusters = $service->cluster(
            $this->cluster->keywords,
            $this->cluster->algorithm,
            minClusterSize: 2,
            similarityThreshold: 0.6,
        );

        $this->cluster->update([
            'clusters' => $clusters,
            'status' => 'completed',
            'processing_error' => null,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $this->cluster->update([
            'status' => 'failed',
            'processing_error' => $exception?->getMessage(),
        ]);
    }
}
