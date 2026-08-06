<?php

namespace Modules\KnowledgeManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\KnowledgeManager\Models\Project;

class AnswerController extends Controller
{
    public function edit(Project $project)
    {
        $project->load('answers');

        return view('knowledgemanager::answers.form', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'answers' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($project, $validated) {
            foreach ($validated['answers'] ?? [] as $section => $questions) {
                foreach ($questions as $key => $answer) {
                    $project->answers()->updateOrCreate(
                        ['section' => $section, 'question_key' => $key],
                        ['answer' => $answer, 'team_id' => $project->team_id]
                    );
                }
            }
        });

        return redirect()->route('knowledgemanager.projects.show', $project)->with('message', __('Knowledge answers saved.'));
    }
}
