@php($comments = \App\Models\Comment::where('commentable_type', get_class($commentable ?? ''))->where('commentable_id', $commentable?->id)->with('user')->latest()->get())

<div class="mt-8 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <h3 class="text-lg font-semibold text-slate-900">{{ __('Comments') }}</h3>

    <form method="POST" action="{{ route('comments.store') }}" class="mt-4">
        @csrf
        <input type="hidden" name="commentable_type" value="{{ get_class($commentable ?? '') }}">
        <input type="hidden" name="commentable_id" value="{{ $commentable?->id }}">
        <textarea name="body" rows="2" placeholder="{{ __('Add a comment... @username to mention') }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        <button type="submit" class="mt-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Post') }}</button>
    </form>

    <div class="mt-4 space-y-3">
        @forelse ($comments as $comment)
            <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-slate-900">{{ $comment->user->name ?? __('Unknown') }}</span>
                    <span class="text-xs text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                </div>
                <p class="mt-1 text-sm text-slate-700">{{ $comment->body }}</p>
                @if ($comment->user_id === auth()->id() || auth()->user()?->isAdmin())
                    <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-rose-600 hover:text-rose-800">{{ __('Delete') }}</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="text-sm text-slate-500">{{ __('No comments yet.') }}</p>
        @endforelse
    </div>
</div>
