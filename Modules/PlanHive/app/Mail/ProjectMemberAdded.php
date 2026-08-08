<?php

namespace Modules\PlanHive\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\PlanHive\Models\Project;

class ProjectMemberAdded extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Project $project, public string $role) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('You have been added to :project', ['project' => $this->project->name]),
        );
    }

    public function content(): Content
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
