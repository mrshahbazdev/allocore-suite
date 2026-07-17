<?php

namespace App\Http\Controllers;

use App\Mail\TeamInvitationMail;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TeamInvitationController extends Controller
{
    public function store(Request $request, Team $team)
    {
        abort_unless($team->owner_id === $request->user()->id, 403);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'role' => 'required|in:member,admin',
        ]);

        if (User::where('email', $validated['email'])->exists()) {
            return back()->with('error', __('This user already has an account. Add them directly.'));
        }

        $existing = $team->invitations()->where('email', $validated['email'])->whereNull('accepted_at')->first();
        if ($existing) {
            return back()->with('warning', __('Invitation already sent to :email', ['email' => $validated['email']]));
        }

        $invitation = $team->invitations()->create([
            'invited_by' => $request->user()->id,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'token' => TeamInvitation::generateToken(),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->send(new TeamInvitationMail($invitation));

        return back()->with('success', __('Invitation sent to :email', ['email' => $invitation->email]));
    }

    public function accept(Request $request, string $token)
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isExpired() || $invitation->isAccepted()) {
            abort(410, __('This invitation has expired or already been used.'));
        }

        $user = Auth::user();

        if (! $user) {
            session([
                'invitation_token' => $token,
                'invitation_email' => $invitation->email,
            ]);

            return redirect()->route('register');
        }

        if ($user->email !== $invitation->email) {
            return back()->with('error', __('This invitation was sent to a different email address.'));
        }

        $invitation->accept($user);
        $user->update(['current_team_id' => $invitation->team_id]);

        return redirect()->route('teams.index')->with('success', __('You have joined :team', ['team' => $invitation->team->name]));
    }

    public function resend(Request $request, TeamInvitation $invitation)
    {
        abort_unless($invitation->team->owner_id === $request->user()->id, 403);

        $invitation->update(['expires_at' => now()->addDays(7)]);
        Mail::to($invitation->email)->send(new TeamInvitationMail($invitation));

        return back()->with('success', __('Invitation resent.'));
    }

    public function destroy(Request $request, TeamInvitation $invitation)
    {
        abort_unless($invitation->team->owner_id === $request->user()->id, 403);

        $invitation->delete();

        return back()->with('success', __('Invitation cancelled.'));
    }
}
