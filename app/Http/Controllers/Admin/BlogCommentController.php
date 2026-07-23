<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = BlogComment::with('post')
            ->when($request->status === 'approved', fn ($q) => $q->where('is_approved', true))
            ->when($request->status === 'pending', fn ($q) => $q->where('is_approved', false))
            ->latest()
            ->paginate(50);

        return view('admin.blog.comments.index', compact('comments'));
    }

    public function approve(BlogComment $comment)
    {
        $comment->update(['is_approved' => true]);

        return back()->with('success', __('Comment approved.'));
    }

    public function destroy(BlogComment $comment)
    {
        $comment->delete();

        return back()->with('success', __('Comment deleted.'));
    }
}
