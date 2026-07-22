<?php

namespace Modules\LoopEngine\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LoopEngine\Models\Process;
use Modules\LoopEngine\Models\ProcessRun;
use Modules\LoopEngine\Models\StepOption;
use Modules\LoopEngine\Services\ProcessEngine;

class RunController extends Controller
{
    public function __construct(protected ProcessEngine $engine) {}

    public function index(): View
    {
        $runs = ProcessRun::with('process')
            ->where('started_by', auth()->id())
            ->latest('started_at')
            ->paginate(20);

        return view('loopengine::runs.index', compact('runs'));
    }

    public function start(Process $process): RedirectResponse
    {
        $run = $this->engine->startRun($process, auth()->user());

        return redirect()->route('loopengine.runs.show', $run);
    }

    public function show(ProcessRun $run): View
    {
        $run->load('process', 'currentStep.options', 'responses');

        if ($run->status === 'completed' || $run->status === 'cancelled') {
            return redirect()->route('loopengine.runs.summary', $run);
        }

        return view('loopengine::runs.show', compact('run'));
    }

    public function answer(Request $request, ProcessRun $run): RedirectResponse
    {
        $step = $run->currentStep;

        if (! $step) {
            return redirect()->route('loopengine.runs.summary', $run);
        }

        $validated = $request->validate([
            'option_id' => 'nullable|exists:loopengine_step_options,id',
            'response_text' => 'nullable|string',
        ]);

        $option = $validated['option_id'] ? StepOption::find($validated['option_id']) : null;

        $result = $this->engine->submitAnswer($run, $step, $option, $validated['response_text'] ?? null, auth()->user());

        if ($result['action'] === 'end') {
            return redirect()->route('loopengine.runs.summary', $run);
        }

        return redirect()->route('loopengine.runs.show', $run);
    }

    public function pause(ProcessRun $run): RedirectResponse
    {
        $this->engine->pauseRun($run, auth()->user());

        return redirect()->route('loopengine.runs.show', $run);
    }

    public function resume(ProcessRun $run): RedirectResponse
    {
        $this->engine->resumeRun($run, auth()->user());

        return redirect()->route('loopengine.runs.show', $run);
    }

    public function cancel(ProcessRun $run): RedirectResponse
    {
        $this->engine->cancelRun($run, auth()->user());

        return redirect()->route('loopengine.runs.index');
    }

    public function summary(ProcessRun $run): View
    {
        $run->load('process', 'responses.step', 'responses.option', 'logs.user');

        return view('loopengine::runs.summary', compact('run'));
    }
}
