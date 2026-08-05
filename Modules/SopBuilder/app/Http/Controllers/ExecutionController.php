<?php

namespace Modules\SopBuilder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\SopBuilder\Models\Completion;
use Modules\SopBuilder\Models\Evidence;
use Modules\SopBuilder\Models\Sop;

class ExecutionController extends Controller
{
    public function show(Sop $sop)
    {
        $sop->load(['steps.checklistItems', 'checklistItems' => fn ($q) => $q->whereNull('step_id'), 'quizzes']);

        return view('sopbuilder::execution.show', compact('sop'));
    }

    public function store(Request $request, Sop $sop)
    {
        $checklist = $request->input('checklist', []);
        $answers = $request->input('answers', []);

        $totalQuizzes = $sop->quizzes->count();
        $correct = 0;

        foreach ($sop->quizzes as $quiz) {
            $given = $answers[$quiz->id] ?? null;
            if ($quiz->answer_type === 'multiple' && is_array($given)) {
                sort($given);
                $expected = is_array($quiz->correct_answer) ? $quiz->correct_answer : explode(',', $quiz->correct_answer);
                sort($expected);
                if ($given === $expected) {
                    $correct++;
                }
            } elseif ($given !== null && (string) $given === (string) $quiz->correct_answer) {
                $correct++;
            }
        }

        $score = $totalQuizzes > 0 ? round(($correct / $totalQuizzes) * 100) : null;

        Completion::create([
            'sop_id' => $sop->id,
            'user_id' => auth()->id(),
            'completed_at' => now(),
            'checked_items' => array_keys($checklist),
            'answers' => $answers,
            'score' => $score,
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('sopbuilder.sops.show', $sop)->with('message', __('SOP completed.').($score !== null ? ' '.__('Score:').' '.$score.'%' : ''));
    }

    public function createEvidence(Sop $sop)
    {
        return view('sopbuilder::execution.evidence', compact('sop'));
    }

    public function storeEvidence(Request $request, Sop $sop)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'notes' => 'nullable|string',
        ]);

        $path = $request->file('file')->store('sop-evidence/'.now()->format('Y-m'), 'public');

        Evidence::create([
            'sop_id' => $sop->id,
            'user_id' => auth()->id(),
            'file_path' => $path,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('sopbuilder.sops.show', $sop)->with('message', __('Evidence uploaded.'));
    }
}
