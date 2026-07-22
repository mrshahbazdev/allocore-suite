<?php

namespace Modules\LeadQuality\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LeadQuality\Models\Contact;
use Modules\LeadQuality\Models\EmailAccount;
use Modules\LeadQuality\Services\LeadEmailService;

class EmailAccountController
{
    public function index(): View
    {
        return view('leadquality::email-accounts.index', [
            'accounts' => EmailAccount::query()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email_address' => 'required|email',
            'provider' => 'nullable|string|max:255',
            'imap_host' => 'nullable|string|max:255',
            'imap_port' => 'nullable|integer',
            'imap_encryption' => 'nullable|string|max:255',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer',
            'smtp_encryption' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        EmailAccount::updateOrCreate(
            [
                'team_id' => auth()->user()->current_team_id,
                'email_address' => $validated['email_address'],
            ],
            $validated + [
                'team_id' => auth()->user()->current_team_id,
                'user_id' => auth()->id(),
                'is_active' => $request->boolean('is_active', true),
            ]
        );

        return redirect()->back()->with('success', __('Email account settings saved!'));
    }

    public function destroy(EmailAccount $emailAccount): RedirectResponse
    {
        $emailAccount->delete();

        return redirect()->back()->with('success', __('Email account removed.'));
    }

    public function test(EmailAccount $emailAccount, LeadEmailService $emailService): RedirectResponse
    {
        $contact = new Contact([
            'email' => auth()->user()->email,
            'name' => auth()->user()->name,
            'team_id' => $emailAccount->team_id,
        ]);

        $result = $emailService->send($contact, __('Test email from LeadOS'), __('This is a test email. Your SMTP settings are working.'), $emailAccount->user);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['success'] ? __('Test email sent.') : $result['message']);
    }
}
