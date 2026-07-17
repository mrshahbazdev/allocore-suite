<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $media = Media::with(['user', 'team'])
            ->when($request->filled('collection'), function ($query) use ($request) {
                $query->where('collection', $request->collection);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('file_name', 'like', '%'.$request->search.'%');
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $collections = Media::distinct()->orderBy('collection')->pluck('collection');

        return view('admin.media.index', compact('media', 'collections'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|max:10240',
            'collection' => 'nullable|string|max:255',
        ]);

        $collection = $validated['collection'] ?? 'default';

        foreach ($request->file('files') as $file) {
            $path = $file->store('media/'.$collection, 'public');

            Media::create([
                'user_id' => auth()->id(),
                'team_id' => auth()->user()?->current_team_id,
                'collection' => $collection,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return redirect()->route('admin.media.index')->with('success', __('admin.media.uploaded'));
    }

    public function destroy(Media $media)
    {
        $media->deleteFile();
        $media->delete();

        return redirect()->route('admin.media.index')->with('success', __('admin.media.deleted'));
    }
}
