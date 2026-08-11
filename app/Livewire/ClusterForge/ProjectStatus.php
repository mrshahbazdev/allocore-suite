<?php

namespace App\Livewire\ClusterForge;

use Livewire\Component;
use Modules\ClusterForge\Models\Project;

class ProjectStatus extends Component
{
    public int $projectId;

    public ?Project $project = null;

    public function mount(int $projectId): void
    {
        $this->projectId = $projectId;
        $this->refreshProject();
    }

    public function render()
    {
        $this->refreshProject();

        return view('livewire.clusterforge.project-status');
    }

    private function refreshProject(): void
    {
        $this->project = Project::find($this->projectId);
    }
}
