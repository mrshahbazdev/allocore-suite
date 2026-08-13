<?php

namespace Modules\PlanHive\Mail;

use App\Mail\TemplatedMailable;
use Illuminate\Mail\Mailables\Content;
use Modules\PlanHive\Models\Project;

class ProjectMemberAdded extends TemplatedMailable
{
    public function __construct(public Project $project, public string $role) {}

    public function templateTool(): string
    {
        return 'planhive';
    }

    public function templateKey(): string
    {
        return 'project-member-added';
    }

    public function templateVariables(): array
    {
        return [
            'projectName' => $this->project->name,
            'role' => $this->role,
            'url' => route('planhive.projects.show', $this->project),
            'buttonText' => __('Open Project'),
            'appName' => config('app.name'),
        ];
    }

    protected function defaultSubject(): string
    {
        return __('You have been added to :project', ['project' => $this->project->name]);
    }

    protected function defaultContent(): Content
    {
        return new Content(
            view: 'planhive::emails.project-member-added',
            with: [
                'projectUrl' => route('planhive.projects.show', $this->project),
                'projectName' => $this->project->name,
                'role' => $this->role,
            ],
        );
    }
}
