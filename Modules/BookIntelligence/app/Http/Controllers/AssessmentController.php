<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BookIntelligence\Models\AssessmentSubmission;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\SkillAssessment;
use Modules\BookIntelligence\Services\SkillAssessmentGenerator;

class AssessmentController extends Controller
{
    public function index(): View
    {
        $assessments = SkillAssessment::query()->with(['book.author', 'role'])->latest()->paginate(12);
        $books = Book::query()->orderBy('title')->get();
        $userSubmissions = AssessmentSubmission::where('user_id', auth()->id())->latest()->take(5)->get();

        return view('bookintelligence::assessments.index', compact('assessments', 'books', 'userSubmissions'));
    }

    public function generate(Book $book, SkillAssessmentGenerator $generator): RedirectResponse
    {
        try {
            $assessment = $generator->generateForBook($book);

            return redirect()->route('bookintelligence.assessments.show', $assessment->id)
                ->with('status', "AI Scenario Assessment generated for '{$book->title}'!");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to generate assessment: ' . $e->getMessage()]);
        }
    }

    public function show(SkillAssessment $assessment): View
    {
        $assessment->loadMissing(['book.author', 'role']);
        $latestSubmission = AssessmentSubmission::where('assessment_id', $assessment->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        return view('bookintelligence::assessments.show', compact('assessment', 'latestSubmission'));
    }

    public function submit(Request $request, SkillAssessment $assessment, SkillAssessmentGenerator $generator): RedirectResponse
    {
        $answers = $request->input('answers', []);

        try {
            $submission = $generator->evaluateSubmission($assessment, $answers, auth()->user());

            return redirect()->route('bookintelligence.assessments.show', $assessment->id)
                ->with('status', 'Assessment completed! Score: ' . $submission->score . '%');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to evaluate assessment: ' . $e->getMessage()]);
        }
    }
}
