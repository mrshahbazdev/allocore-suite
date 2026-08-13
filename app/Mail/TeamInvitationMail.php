<?php

namespace App\Mail;

use App\Models\TeamInvitation;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Support\Facades\DB;

class TeamInvitationMail extends TemplatedMailable
{
    public function __construct(public TeamInvitation $invitation) {}

    public function templateTool(): string
    {
        return 'core';
    }

    public function templateKey(): string
    {
        return 'team-invitation';
    }

    public function templateVariables(): array
    {
        return [
            'teamName' => $this->invitation->team->name,
            'inviterName' => $this->invitation->inviter->name,
            'acceptUrl' => route('teams.invitations.accept', $this->invitation->token),
            'projectName' => $this->projectName(),
        ];
    }

    protected function defaultSubject(): string
    {
        $projectName = $this->projectName();

        return $projectName
            ? __('You are invited to join :team on project :project', ['team' => $this->invitation->team->name, 'project' => $projectName])
            : __('You are invited to join :team', ['team' => $this->invitation->team->name]);
    }

    protected function defaultContent(): Content
    {
        return new Content(
            view: 'emails.team-invitation',
            with: $this->templateVariables(),
        );
    }

    protected function projectName(): ?string
    {
        if (! $this->invitation->project_id) {
            return null;
        }

        $project = DB::table('planhive_projects')->find($this->invitation->project_id);

        return $project?->name;
    }
}
