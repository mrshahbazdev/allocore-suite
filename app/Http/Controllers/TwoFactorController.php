<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class TwoFactorController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->two_factor_confirmed_at) {
            return view('profile.two-factor', [
                'enabled' => true,
                'recoveryCodes' => null,
            ]);
        }

        $secret = $request->session()->get('two_factor_secret', Google2FA::generateSecretKey());
        $request->session()->put('two_factor_secret', $secret);

        $qrCode = Google2FA::getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret,
            200
        );

        return view('profile.two-factor', [
            'enabled' => false,
            'secret' => $secret,
            'qrCode' => $qrCode,
            'recoveryCodes' => $this->recoveryCodes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->two_factor_confirmed_at) {
            return redirect()->route('two-factor.index');
        }

        $validated = $request->validate(['code' => ['required', 'string', 'size:6']]);
        $secret = $request->session()->get('two_factor_secret');

        if (! $secret || ! Google2FA::verifyKey($secret, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => __('Invalid two-factor authentication code.'),
            ]);
        }

        $user->update([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($request->session()->get('two_factor_recovery_codes', []))),
            'two_factor_confirmed_at' => now(),
        ]);

        $request->session()->forget(['two_factor_secret', 'two_factor_recovery_codes']);

        return redirect()->route('two-factor.index')->with('status', __('Two-factor authentication enabled.'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $request->user()->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return redirect()->route('two-factor.index')->with('status', __('Two-factor authentication disabled.'));
    }

    public function regenerate(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $request->user()->update([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($this->recoveryCodes())),
        ]);

        return redirect()->route('two-factor.index')->with('status', __('Recovery codes regenerated.'));
    }

    protected function recoveryCodes(): array
    {
        return collect(range(1, 8))->map(fn () => bin2hex(random_bytes(4)))->all();
    }
}
