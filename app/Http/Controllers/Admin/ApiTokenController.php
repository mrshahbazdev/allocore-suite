<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiTokenController extends Controller
{
    public function index(Request $request)
    {
        $tokens = ApiToken::with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('email', 'like', '%'.$request->search.'%');
                })->orWhere('name', 'like', '%'.$request->search.'%');
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.api-tokens.index', compact('tokens'));
    }

    public function create()
    {
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('admin.api-tokens.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'abilities' => 'nullable|string',
            'expires_at' => 'nullable|date',
        ]);

        $plainToken = ApiToken::generatePlainToken();

        $token = ApiToken::create([
            'user_id' => $validated['user_id'],
            'name' => $validated['name'],
            'token' => Hash::make($plainToken),
            'abilities' => $this->parseAbilities($validated['abilities'] ?? null),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return redirect()->route('admin.api-tokens.index')->with([
            'success' => __('admin.api_tokens.created'),
            'plain_token' => $plainToken,
        ]);
    }

    public function destroy(ApiToken $apiToken)
    {
        $apiToken->delete();

        return redirect()->route('admin.api-tokens.index')->with('success', __('admin.api_tokens.deleted'));
    }

    private function parseAbilities(?string $value): array
    {
        if (blank($value)) {
            return ['*'];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
