<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TestMail;
use App\Models\MailSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailSettingController extends Controller
{
    public function index()
    {
        $setting = MailSetting::query()->global()->first();

        return view('admin.mail-settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'driver' => 'required|string|max:50',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:1000',
            'encryption' => 'nullable|string|in:tls,ssl,none',
            'from_address' => 'required|email|max:255',
            'from_name' => 'nullable|string|max:255',
        ]);

        $validated['encryption'] = $validated['encryption'] === 'none' ? null : $validated['encryption'];
        $validated['user_id'] = null;

        $setting = MailSetting::query()->global()->first();

        if ($setting) {
            $update = $validated;

            if (empty($update['password'])) {
                unset($update['password']);
            }

            $setting->update($update);
        } else {
            MailSetting::create($validated);
        }

        return back()->with('success', __('mail.admin_updated'));
    }

    public function sendTest(Request $request)
    {
        $setting = MailSetting::query()->global()->first();

        if (! $setting?->isUsable()) {
            return back()->with('error', __('Please save a valid SMTP configuration before sending a test email.'));
        }

        $user = $request->user();

        try {
            Mail::to($user->email)->send(new TestMail($user));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', __('Test email failed: :message', ['message' => $e->getMessage()]));
        }

        return back()->with('success', __('Test email sent to :email', ['email' => $user->email]));
    }
}
