<?php

namespace App\Livewire\ClusterForge;

use Livewire\Component;
use Modules\ClusterForge\Models\Project;

class ProjectStatus extends Component
{
    public int $projectId;

    public ?Project $project = null;

    public string $previousStatus = '';

    public function mount(int $projectId): void
    {
        $this->projectId = $projectId;
        $this->refreshProject();
        $this->previousStatus = $this->project?->status ?? '';
    }

    public function render()
    {
        $this->refreshProject();

        if ($this->project) {
            $status = $this->project->status;

            if ($this->previousStatus !== $status && in_array($status, [Project::STATUS_COMPLETED, Project::STATUS_FAILED], true)) {
                $this->previousStatus = $status;
                $this->js('window.location.reload()');
            } else {
                $this->previousStatus = $status;
            }
        }

        return view('clusterforge::livewire.project-status');
    }

    private function refreshProject(): void
    {
        $this->project = Project::find($this->projectId);
    }
}
