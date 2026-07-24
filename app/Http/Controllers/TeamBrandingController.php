<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Services\SslAutomation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TeamBrandingController extends Controller
{
    public function __construct(protected SslAutomation $ssl) {}

    public function edit(Request $request, Team $team)
    {
        abort_if($team->owner_id !== $request->user()->id && ! $request->user()->isAdmin(), 403);

        return view('teams.branding', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        abort_if($team->owner_id !== $request->user()->id && ! $request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'subdomain' => 'nullable|string|max:100|unique:teams,subdomain,'.$team->id,
            'custom_domain' => 'nullable|string|max:255|unique:teams,custom_domain,'.$team->id,
            'logo' => 'nullable|url|max:1000',
            'favicon' => 'nullable|url|max:1000',
            'primary_color' => 'nullable|hex_color|max:7',
            'accent_color' => 'nullable|hex_color|max:7',
        ]);

        if (($validated['custom_domain'] ?? null) !== $team->custom_domain) {
            $validated['custom_domain_verified_at'] = null;
            $validated['ssl_status'] = 'pending';
            $validated['ssl_issued_at'] = null;
            $validated['ssl_expires_at'] = null;
            $validated['ssl_last_error'] = null;
        }

        $team->update($validated);

        return back()->with('success', __('Team branding updated.'));
    }

    public function verifyDomain(Request $request, Team $team)
    {
        abort_if($team->owner_id !== $request->user()->id && ! $request->user()->isAdmin(), 403);

        if ($this->ssl->verifyDns($team)) {
            return back()->with('success', __('Custom domain DNS verified.'));
        }

        return back()->with('error', __('Custom domain DNS could not be verified. Make sure an A or AAAA record exists.'));
    }

    public function requestSsl(Request $request, Team $team)
    {
        abort_if($team->owner_id !== $request->user()->id && ! $request->user()->isAdmin(), 403);

        $result = $this->ssl->request($team);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
