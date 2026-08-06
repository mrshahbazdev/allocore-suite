<?php

namespace Modules\PlanHive\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\PlanHive\Models\Document;
use Modules\PlanHive\Models\Project;

class DocumentController extends Controller
{
    public function index(Project $project): View
    {
        $documents = $project->documents()->orderBy('position')->latest()->paginate(25);

        return view('planhive::documents.index', compact('project', 'documents'));
    }

    public function show(Document $document): View
    {
        return view('planhive::documents.show', compact('document'));
    }

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
            'position' => $project->documents()->max('position') + 1,
        ]);

        return redirect()->route('planhive.projects.show', $project)->with('success', __('Document uploaded.'));
    }

    public function edit(Document $document): View
    {
        return view('planhive::documents.form', ['project' => $document->project, 'document' => $document, 'projects' => Project::orderBy('name')->get()]);
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'project_id' => ['nullable', Rule::exists('planhive_projects', 'id')],
            'file' => 'nullable|file|max:10240',
        ]);

        $document->title = $validated['title'];

        if (! empty($validated['project_id']) && $validated['project_id'] != $document->project_id) {
            $document->project_id = $validated['project_id'];
            $document->position = 0;
        }

        if ($request->hasFile('file')) {
            if (Storage::disk('public')->exists($document->path)) {
                Storage::disk('public')->delete($document->path);
            }

            $file = $request->file('file');
            $document->path = $file->store('planhive/documents/'.$document->team_id, 'public');
            $document->mime_type = $file->getMimeType();
            $document->size = $file->getSize();
        }

        $document->save();

        return redirect()->route('planhive.documents.index', $document->project)->with('success', __('Document updated.'));
    }

    public function preview(Document $document): Response
    {
        if (! Storage::disk('public')->exists($document->path)) {
            abort(404, __('File not found.'));
        }

        return Storage::disk('public')->response($document->path, $document->title);
    }

    public function download(Document $document)
    {
        if (! Storage::disk('public')->exists($document->path)) {
            return back()->with('error', __('File not found.'));
        }

        return Storage::disk('public')->download($document->path, $document->title);
    }

    public function move(Document $document, string $direction): RedirectResponse
    {
        $project = $document->project;
        $documents = $project->documents()->orderBy('position')->orderBy('id')->get();
        $index = $documents->search(fn ($d) => $d->id === $document->id);

        if ($index === false) {
            return back()->with('error', __('Document not found.'));
        }

        $swapWith = match ($direction) {
            'up' => $documents->get($index - 1),
            'down' => $documents->get($index + 1),
            default => null,
        };

        if ($swapWith) {
            $temp = $document->position;
            $document->position = $swapWith->position;
            $swapWith->position = $temp;
            $document->save();
            $swapWith->save();
        }

        return back()->with('success', __('Document moved.'));
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
