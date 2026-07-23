<?php

namespace App\Http\Controllers;

use App\Models\AiChatMessage;
use App\Services\AiAssistant;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AiAssistantController extends Controller
{
    public function __construct(protected AiAssistant $assistant) {}

    public function index(Request $request)
    {
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

        $reply = $this->assistant->ask(
            $user,
            $validated['message'],
            $validated['module_key'] ?? null,
            $validated['page_url'] ?? null
        );

        AiChatMessage::create([
            'user_id' => $user->id,
            'team_id' => $user->current_team_id,
            'role' => 'assistant',
            'content' => $reply,
            'module_key' => $validated['module_key'] ?? null,
            'page_url' => $validated['page_url'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['reply' => $reply]);
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
}
