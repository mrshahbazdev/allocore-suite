<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserApiTokenController extends Controller
{
    public function index(Request $request): View
    {
        $tokens = $request->user()->apiTokens()->latest()->paginate(20);

        return view('profile.api-tokens', compact('tokens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['nullable', 'string'],
        ]);

        $plainToken = ApiToken::generatePlainToken();

        $request->user()->apiTokens()->create([
            'name' => $validated['name'],
            'token' => Hash::make($plainToken),
            'abilities' => $this->parseAbilities($validated['abilities'] ?? null),
        ]);

        return redirect()->route('profile.api-tokens.index')->with([
            'status' => __('API token created.'),
            'plain_token' => $plainToken,
        ]);
    }

    public function destroy(Request $request, ApiToken $token): RedirectResponse
    {
        abort_if($token->user_id !== $request->user()->id, 403);

        $token->delete();

        return redirect()->route('profile.api-tokens.index')->with('status', __('API token deleted.'));
    }

    private function parseAbilities(?string $value): array
    {
        if (blank($value)) {
            return ['*'];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
