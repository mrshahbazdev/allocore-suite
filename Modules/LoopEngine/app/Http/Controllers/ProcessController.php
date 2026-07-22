<?php

namespace Modules\LoopEngine\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LoopEngine\Models\Process;
use Modules\LoopEngine\Models\ProcessStep;
use Modules\LoopEngine\Models\StepOption;
use Modules\LoopEngine\Models\StepTransition;

class ProcessController extends Controller
{
    public function index(Request $request): View
    {
        $query = Process::query()->where('is_latest_version', true);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request): void {
                $q->where('name_en', 'like', '%'.$request->search.'%')
                    ->orWhere('name_de', 'like', '%'.$request->search.'%');
            });
        }

        $processes = $query->latest()->paginate(15)->withQueryString();

        return view('loopengine::processes.index', compact('processes'));
    }

    public function create(): View
    {
        return view('loopengine::processes.form', ['process' => new Process]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->processRules());

        Process::create($validated + ['status' => 'draft']);

        return redirect()->route('loopengine.processes.index')->with('success', __('Process created.'));
    }

    public function show(Process $process): View
    {
        $process->load('steps.options.transitions');

        return view('loopengine::processes.show', compact('process'));
    }

    public function edit(Process $process): View
    {
        $process->load('steps.options.transitions.targetStep', 'steps.options.transitions.targetProcess');

        return view('loopengine::processes.edit', compact('process'));
    }

    public function update(Request $request, Process $process): RedirectResponse
    {
        $validated = $request->validate($this->processRules());

        $process->update($validated);

        return redirect()->route('loopengine.processes.edit', $process)->with('success', __('Process updated.'));
    }

    public function destroy(Process $process): RedirectResponse
    {
        $process->delete();

        return redirect()->route('loopengine.processes.index')->with('success', __('Process deleted.'));
    }

    public function duplicate(Process $process): RedirectResponse
    {
        $clone = $process->duplicate(auth()->user());

        return redirect()->route('loopengine.processes.edit', $clone)->with('success', __('Process duplicated.'));
    }

    public function newVersion(Process $process): RedirectResponse
    {
        $newVersion = $process->createNewVersion(auth()->user());

        return redirect()->route('loopengine.processes.edit', $newVersion)->with('success', __('New version created.'));
    }

    public function activate(Process $process): RedirectResponse
    {
        $process->update(['status' => 'active']);

        return redirect()->route('loopengine.processes.index')->with('success', __('Process activated.'));
    }

    public function archive(Process $process): RedirectResponse
    {
        $process->update(['status' => 'archived']);

        return redirect()->route('loopengine.processes.index')->with('success', __('Process archived.'));
    }

    public function reorderSteps(Request $request, Process $process): RedirectResponse
    {
        $orders = $request->validate(['orders' => 'required|array']);

        foreach ($orders['orders'] as $id => $order) {
            ProcessStep::where('id', $id)->where('process_id', $process->id)->update(['order' => $order]);
        }

        return redirect()->route('loopengine.processes.edit', $process)->with('success', __('Steps reordered.'));
    }

    public function storeStep(Request $request, Process $process): RedirectResponse
    {
        $validated = $request->validate([
            'question_en' => 'required|string|max:1000',
            'question_de' => 'nullable|string|max:1000',
            'help_text_en' => 'nullable|string',
            'help_text_de' => 'nullable|string',
            'step_type' => 'nullable|string|in:question,decision,loop_check,info,end',
            'is_loop_checkpoint' => 'nullable|boolean',
            'is_required' => 'nullable|boolean',
            'max_loops' => 'nullable|integer|min:0',
        ]);

        $maxOrder = $process->steps()->max('order') ?? 0;
        $validated['order'] = $maxOrder + 1;
        $validated['is_loop_checkpoint'] = $request->boolean('is_loop_checkpoint');
        $validated['is_required'] = $request->boolean('is_required', true);
        $validated['team_id'] = $process->team_id;
        $validated['process_id'] = $process->id;

        ProcessStep::create($validated);

        return redirect()->route('loopengine.processes.edit', $process)->with('success', __('Step added.'));
    }

    public function editStep(ProcessStep $step): View
    {
        $step->load('process', 'options', 'transitions.targetStep', 'transitions.targetProcess');

        return view('loopengine::processes.steps.form', compact('step'));
    }

    public function updateStep(Request $request, ProcessStep $step): RedirectResponse
    {
        $validated = $request->validate([
            'question_en' => 'required|string|max:1000',
            'question_de' => 'nullable|string|max:1000',
            'help_text_en' => 'nullable|string',
            'help_text_de' => 'nullable|string',
            'step_type' => 'nullable|string|in:question,decision,loop_check,info,end',
            'is_loop_checkpoint' => 'nullable|boolean',
            'is_required' => 'nullable|boolean',
            'max_loops' => 'nullable|integer|min:0',
            'order' => 'required|integer|min:0',
        ]);

        $validated['is_loop_checkpoint'] = $request->boolean('is_loop_checkpoint');
        $validated['is_required'] = $request->boolean('is_required', true);

        $step->update($validated);

        return redirect()->route('loopengine.processes.edit', $step->process)->with('success', __('Step updated.'));
    }

    public function destroyStep(ProcessStep $step): RedirectResponse
    {
        $process = $step->process;
        $step->delete();

        return redirect()->route('loopengine.processes.edit', $process)->with('success', __('Step deleted.'));
    }

    public function storeOption(Request $request, ProcessStep $step): RedirectResponse
    {
        $validated = $request->validate([
            'label_en' => 'required|string|max:255',
            'label_de' => 'nullable|string|max:255',
            'value' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
        ]);

        $maxOrder = $step->options()->max('order') ?? 0;
        $validated['order'] = $maxOrder + 1;
        $validated['team_id'] = $step->team_id;
        $validated['step_id'] = $step->id;

        StepOption::create($validated);

        return redirect()->route('loopengine.steps.edit', $step)->with('success', __('Option added.'));
    }

    public function destroyOption(StepOption $option): RedirectResponse
    {
        $step = $option->step;
        $option->delete();

        return redirect()->route('loopengine.steps.edit', $step)->with('success', __('Option deleted.'));
    }

    public function storeTransition(Request $request, ProcessStep $step): RedirectResponse
    {
        $validated = $request->validate([
            'option_id' => 'nullable|exists:loopengine_step_options,id',
            'action_type' => 'required|string|in:next_step,goto_step,start_process,loop_back,end',
            'target_step_id' => 'nullable|exists:loopengine_process_steps,id',
            'target_process_id' => 'nullable|exists:loopengine_processes,id',
        ]);

        $validated['team_id'] = $step->team_id;
        $validated['step_id'] = $step->id;

        StepTransition::create($validated);

        return redirect()->route('loopengine.steps.edit', $step)->with('success', __('Transition added.'));
    }

    public function destroyTransition(StepTransition $transition): RedirectResponse
    {
        $step = $transition->step;
        $transition->delete();

        return redirect()->route('loopengine.steps.edit', $step)->with('success', __('Transition deleted.'));
    }

    private function processRules(): array
    {
        return [
            'name_en' => 'required|string|max:255',
            'name_de' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_de' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
        ];
    }
}
