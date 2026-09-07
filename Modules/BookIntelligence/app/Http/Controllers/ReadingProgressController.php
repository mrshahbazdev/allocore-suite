<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\ReadingProgress;

class ReadingProgressController extends Controller
{
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'reading_status' => ['required', Rule::in(['unassigned', ...ReadingProgress::STATUSES])],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'reading_notes' => ['nullable', 'string'],
        ]);

        if ($validated['reading_status'] === 'unassigned') {
            $book->progressRecords()->where('user_id', $request->user()->id)->delete();
        } else {
            $existing = $book->progressRecords()->where('user_id', $request->user()->id)->first();
            $progress = $validated['reading_status'] === 'read'
                ? 100
                : (int) ($validated['progress_percent'] ?? 0);

            $book->progressRecords()->updateOrCreate(
                ['user_id' => $request->user()->id],
                [
                    'team_id' => $request->user()->current_team_id,
                    'status' => $validated['reading_status'],
                    'progress_percent' => $progress,
                    'started_at' => $validated['reading_status'] === 'planned'
                        ? null
                        : ($existing?->started_at ?? now()->toDateString()),
                    'completed_at' => $validated['reading_status'] === 'read' ? now()->toDateString() : null,
                    'notes' => $validated['reading_notes'] ?? null,
                ],
            );
        }

        return back()->with('success', __('Reading progress updated.'));
    }
}
