<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\ChallengeSubmission;
use Modules\BookIntelligence\Models\PracticalChallenge;
use Modules\BookIntelligence\Services\PracticalChallengeGenerator;

class ChallengeController extends Controller
{
    public function index(): View
    {
        $challenges = PracticalChallenge::query()->with(['book.author', 'role'])->latest()->paginate(12);
        $books = Book::query()->orderBy('title')->get();
        $userSubmissions = ChallengeSubmission::where('user_id', auth()->id())->with('challenge')->latest()->get();

        return view('bookintelligence::challenges.index', compact('challenges', 'books', 'userSubmissions'));
    }

    public function generate(Book $book, PracticalChallengeGenerator $generator): RedirectResponse
    {
        @set_time_limit(300);
        try {
            $challenge = $generator->generateForBook($book);

            return redirect()->route('bookintelligence.challenges.show', $challenge->id)
                ->with('status', "Practical simulation challenge generated for '{$book->title}'!");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to generate challenge: ' . $e->getMessage()]);
        }
    }

    public function show(PracticalChallenge $challenge): View
    {
        $challenge->loadMissing(['book.author', 'role']);
        $latestSubmission = ChallengeSubmission::where('challenge_id', $challenge->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        return view('bookintelligence::challenges.show', compact('challenge', 'latestSubmission'));
    }

    public function submit(Request $request, PracticalChallenge $challenge, PracticalChallengeGenerator $generator): RedirectResponse
    {
        $validated = $request->validate([
            'submission_text' => ['required', 'string', 'min:50'],
            'reflection_answers' => ['nullable', 'array'],
        ]);

        $submission = ChallengeSubmission::create([
            'team_id' => $challenge->team_id,
            'challenge_id' => $challenge->id,
            'user_id' => auth()->id(),
            'submission_text' => $validated['submission_text'],
            'reflection_answers' => $validated['reflection_answers'] ?? [],
            'status' => ChallengeSubmission::STATUS_SUBMITTED,
        ]);

        // Auto-grade with AI Evaluator
        try {
            $generator->gradeSubmission($submission);

            return redirect()->route('bookintelligence.challenges.show', $challenge->id)
                ->with('status', 'Challenge submitted and graded! Score: ' . $submission->grade_score . '%');
        } catch (\Throwable $e) {
            return redirect()->route('bookintelligence.challenges.show', $challenge->id)
                ->with('status', 'Challenge submitted successfully and queued for review.');
        }
    }
}
