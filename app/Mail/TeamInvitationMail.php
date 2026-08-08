<?php

namespace App\Mail;

use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TeamInvitation $invitation) {}

    public function envelope(): Envelope
    {
        $projectName = $this->projectName();
        $subject = $projectName
            ? __('You are invited to join :team on project :project', ['team' => $this->invitation->team->name, 'project' => $projectName])
            : __('You are invited to join :team', ['team' => $this->invitation->team->name]);

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.team-invitation',
            with: [
                'acceptUrl' => route('teams.invitations.accept', $this->invitation->token),
                'teamName' => $this->invitation->team->name,
                'inviterName' => $this->invitation->inviter->name,
                'projectName' => $this->projectName(),
            ],
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
