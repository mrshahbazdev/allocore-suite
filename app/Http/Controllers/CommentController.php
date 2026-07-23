<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\AlertNotification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|integer',
            'body' => 'required|string|max:2000',
        ]);

        $class = $this->resolveModel($data['commentable_type']);
        abort_if(! $class || ! class_exists($class), 404);

        $model = $class::findOrFail($data['commentable_id']);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'team_id' => $request->user()->current_team_id,
            'commentable_type' => $class,
            'commentable_id' => $model->id,
            'body' => $data['body'],
            'mentions' => $this->extractMentions($data['body']),
        ]);

        $this->notifyMentions($comment, $request->user());

        return back()->with('success', __('Comment added.'));
    }

    public function destroy(Comment $comment)
    {
        abort_if($comment->user_id !== auth()->id() && ! auth()->user()?->isAdmin(), 403);
        $comment->delete();

        return back()->with('success', __('Comment deleted.'));
    }

    protected function resolveModel(string $type): ?string
    {
        $map = config('commentables', []);

        return $map[$type] ?? (Str::startsWith($type, 'Modules\\') || Str::startsWith($type, 'App\\Models\\') ? $type : null);
    }

    protected function extractMentions(string $body): array
    {
        preg_match_all('/@([a-zA-Z0-9_\.]+)/', $body, $matches);

        return $matches[1] ?? [];
    }

    protected function notifyMentions(Comment $comment, User $author): void
    {
        foreach ($comment->mentions ?? [] as $username) {
            $user = User::where('email', $username)->orWhere('name', $username)->first();

            if ($user && $user->id !== $author->id) {
                $user->notify(new AlertNotification(
                    new Alert([
                        'name' => __('You were mentioned'),
                        'metric' => 'custom',
                        'operator' => '>',
                        'threshold' => 0,
                    ]),
                    0
                ));
            }
        }
    }
}
