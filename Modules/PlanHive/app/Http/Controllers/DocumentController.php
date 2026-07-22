<?php

namespace Modules\PlanHive\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\PlanHive\Models\Document;
use Modules\PlanHive\Models\Project;

class DocumentController extends Controller
{
    public function create(Project $project): View
    {
        return view('planhive::documents.form', ['project' => $project, 'document' => new Document]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('planhive/documents/'.$project->team_id, 'public');

        $project->documents()->create([
            'team_id' => $project->team_id,
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return redirect()->route('planhive.projects.show', $project)->with('success', __('Document uploaded.'));
    }

    public function download(Document $document)
    {
        if (! Storage::disk('public')->exists($document->path)) {
            return back()->with('error', __('File not found.'));
        }

        return Storage::disk('public')->download($document->path, $document->title);
    }

    public function destroy(Document $document): RedirectResponse
    {
        $project = $document->project;

        if (Storage::disk('public')->exists($document->path)) {
            Storage::disk('public')->delete($document->path);
        }

        $document->delete();

        return redirect()->route('planhive.projects.show', $project)->with('success', __('Document deleted.'));
    }
}
