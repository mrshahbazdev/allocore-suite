<?php

namespace App\Http\Controllers;

use App\Models\AiChatMessage;
use App\Models\User;
use App\Services\AiAssistant;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AiAssistantController extends Controller
{
    public function __construct(protected AiAssistant $assistant) {}

    public function index(Request $request)
    {
        $this->authorizeAssistant($request->user());

        $messages = AiChatMessage::where('user_id', $request->user()->id)
            ->where('team_id', $request->user()->current_team_id)
            ->latest()
            ->limit(50)
            ->get()
            ->sortBy('id');

        return view('ai-assistant.index', compact('messages'));
    }

    public function store(Request $request)
    {
        $this->authorizeAssistant($request->user());

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'module_key' => 'nullable|string|max:50',
            'page_url' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        AiChatMessage::create([
            'user_id' => $user->id,
            'team_id' => $user->current_team_id,
            'role' => 'user',
            'content' => $validated['message'],
            'module_key' => $validated['module_key'] ?? null,
            'page_url' => $validated['page_url'] ?? null,
        ]);

        $result = $this->assistant->ask(
            $user,
            $validated['message'],
            $validated['module_key'] ?? null,
            $validated['page_url'] ?? null
        );

        AiChatMessage::create([
            'user_id' => $user->id,
            'team_id' => $user->current_team_id,
            'role' => 'assistant',
            'content' => $result['reply'],
            'sources' => $result['sources'],
            'module_key' => $validated['module_key'] ?? null,
            'page_url' => $validated['page_url'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'reply' => $result['reply'],
                'sources' => $result['sources'],
            ]);
        }

        return back();
    }

    public function destroy(Request $request)
    {
        AiChatMessage::where('user_id', $request->user()->id)
            ->where('team_id', $request->user()->current_team_id)
            ->delete();

        return redirect()->route('assistant.index');
    }

    protected function authorizeAssistant(User $user): void
    {
        if ($user->isAdmin() || $user->isOwner() || $user->hasAnyRole(['employee', 'saas-developer', 'senior-management', 'quality'])) {
            return;
        }

        abort(403, __('You do not have access to the AI assistant.'));
    }
}
