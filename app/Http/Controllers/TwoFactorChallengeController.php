<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View
    {
        if (! $request->session()->has('login.2fa_pending')) {
            return view('auth.two-factor-challenge');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request)
    {
        $pending = $request->session()->get('login.2fa_pending');

        if (! is_array($pending) || empty($pending['id'])) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'code' => ['required_without:recovery_code', 'nullable', 'string', 'size:6'],
            'recovery_code' => ['required_without:code', 'nullable', 'string'],
        ]);

        $user = User::find($pending['id']);

        if (! $user || ! $user->two_factor_confirmed_at) {
            $request->session()->forget('login.2fa_pending');

            return redirect()->route('login');
        }

        if (! empty($validated['code'])) {
            $valid = Google2FA::verifyKey(Crypt::decryptString($user->two_factor_secret), $validated['code']);
        } else {
            $valid = $this->validRecoveryCode($user, $validated['recovery_code']);
        }

        if (! $valid) {
            throw ValidationException::withMessages([
                'code' => __('Invalid two-factor authentication code.'),
            ]);
        }

        Auth::login($user, $pending['remember'] ?? false);
        $request->session()->regenerate();
        $request->session()->forget('login.2fa_pending');

        return redirect()->intended(route('dashboard'));
    }

    protected function validRecoveryCode($user, string $code): bool
    {
        $codes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes) ?: '[]', true);

        if (in_array($code, $codes, true)) {
            $user->update([
                'two_factor_recovery_codes' => Crypt::encryptString(json_encode(array_values(array_diff($codes, [$code])))),
            ]);

            return true;
        }

        return false;
    }
}
