<?php

namespace Modules\InvoiceMaker\Livewire\Settings;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Team extends Component
{
    public string $email = '';

    public string $role = 'viewer';

    protected $rules = [
        'email' => 'required|email|unique:invitations,email',
        'role' => 'required|in:admin,viewer',
    ];

    public function invite(): void
    {
        $this->authorize('manage-team', app(InvoiceMakerContext::class)->profile());

        $this->validate();

        // Check if user already exists in this business
        if (app(InvoiceMakerContext::class)->profile()->users()->where('email', $this->email)->exists()) {
            $this->addError('email', 'This user is already a member of your team.');

            return;
        }

        app(InvoiceMakerContext::class)->profile()->invitations()->create([
            'email' => $this->email,
            'role' => $this->role,
            'token' => Str::random(40),
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $this->email = '';
        $this->role = 'viewer';

        session()->flash('message', 'Invitation sent successfully.');
    }

    public function cancelInvitation(int $id): void
    {
        $this->authorize('manage-team', app(InvoiceMakerContext::class)->profile());

        $invitation = app(InvoiceMakerContext::class)->profile()->invitations()->findOrFail($id);
        $invitation->delete();

        session()->flash('message', 'Invitation cancelled.');
    }

    public function removeMember(int $id): void
    {
        $this->authorize('manage-team', app(InvoiceMakerContext::class)->profile());

        $user = app(InvoiceMakerContext::class)->profile()->users()->findOrFail($id);

        if ($user->id === Auth::id()) {
            session()->flash('error', 'You cannot remove yourself.');

            return;
        }

        if ($user->isOwner()) {
            session()->flash('error', 'You cannot remove the business owner.');

            return;
        }

        $user->update(['team_id' => null, 'role' => 'user']);

        session()->flash('message', 'Team member removed.');
    }

    public function render()
    {
        $business = app(InvoiceMakerContext::class)->profile();
        $members = $business->users()->orderBy('role')->get();
        $invitations = $business->invitations()->whereNull('accepted_at')->where('expires_at', '>', now())->get();

        return view('invoicemaker::livewire.settings.team', [
            'members' => $members,
            'invitations' => $invitations,
        ]);
    }
}
