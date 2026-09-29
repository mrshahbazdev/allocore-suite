<?php

namespace Modules\InvoiceMaker\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\InvoiceMaker\Services\AllocoreReporter;

class PushAllocoreMetric implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $type, public array $body = []) {}

    public function handle(AllocoreReporter $reporter): void
    {
        $reporter->push($this->type, $this->body);
    }
}
