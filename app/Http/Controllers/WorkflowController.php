<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class WorkflowController extends Controller
{
    public function index(Request $request)
    {
        $workflows = Workflow::where('team_id', $request->user()->current_team_id)
            ->latest()
            ->get();

        return view('workflows.index', compact('workflows'));
    }

    public function create()
    {
        return view('workflows.form', ['workflow' => new Workflow]);
    }

    public function store(Request $request)
    {
        $data = $this->validateWorkflow($request);
        $data['user_id'] = $request->user()->id;
        $data['team_id'] = $request->user()->current_team_id;

        Workflow::create($data);

        return redirect()->route('workflows.index')->with('success', __('Workflow created.'));
    }

    public function edit(Workflow $workflow)
    {
        $this->authorizeAccess($workflow);

        return view('workflows.form', compact('workflow'));
    }

    public function update(Request $request, Workflow $workflow)
    {
        $this->authorizeAccess($workflow);

        $workflow->update($this->validateWorkflow($request, $workflow));

        return redirect()->route('workflows.index')->with('success', __('Workflow updated.'));
    }

    public function destroy(Workflow $workflow)
    {
        $this->authorizeAccess($workflow);
        $workflow->delete();

        return redirect()->route('workflows.index')->with('success', __('Workflow deleted.'));
    }

    protected function validateWorkflow(Request $request, ?Workflow $workflow = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'trigger_event' => ['required', Rule::in(['created', 'updated', 'deleted'])],
            'subject_type' => 'nullable|string|max:255',
            'action' => ['required', Rule::in(['send_notification'])],
            'action_payload.message' => 'required|string|max:500',
            'is_active' => 'boolean',
        ]);
    }

    protected function authorizeAccess(Workflow $workflow): void
    {
        abort_if($workflow->team_id !== auth()->user()?->current_team_id, 403);
    }
}
