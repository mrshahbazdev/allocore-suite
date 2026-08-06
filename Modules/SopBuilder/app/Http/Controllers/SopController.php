<?php

namespace Modules\SopBuilder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\SopBuilder\Models\Category;
use Modules\SopBuilder\Models\Sop;

class SopController extends Controller
{
    public function index(Request $request)
    {
        $query = Sop::with('category')->orderByDesc('updated_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $search = '%'.$request->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('description', 'like', $search)
                    ->orWhere('who', 'like', $search);
            });
        }

        $sops = $query->get();
        $categories = Category::orderBy('name')->get();

        return view('sopbuilder::sops.index', compact('sops', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('sopbuilder::sops.form', [
            'sop' => null,
            'categories' => $categories,
            'steps' => collect(),
            'checklist' => collect(),
            'quizzes' => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateSop($request);

        $sop = DB::transaction(function () use ($data) {
            $sop = Sop::create($data['sop']);
            $this->syncNested($sop, $data);

            return $sop;
        });

        return redirect()->route('sopbuilder.sops.show', $sop)->with('message', __('SOP created.'));
    }

    public function show(Sop $sop)
    {
        $sop->load(['category', 'steps.checklistItems', 'checklistItems', 'quizzes', 'completions.user']);

        return view('sopbuilder::sops.show', compact('sop'));
    }

    public function edit(Sop $sop)
    {
        $sop->load(['steps.checklistItems', 'checklistItems' => fn ($q) => $q->whereNull('step_id'), 'quizzes']);
        $categories = Category::orderBy('name')->get();

        return view('sopbuilder::sops.form', [
            'sop' => $sop,
            'categories' => $categories,
            'steps' => $sop->steps,
            'checklist' => $sop->checklistItems()->whereNull('step_id')->get(),
            'quizzes' => $sop->quizzes,
        ]);
    }

    public function update(Request $request, Sop $sop)
    {
        $data = $this->validateSop($request, $sop);

        DB::transaction(function () use ($sop, $data) {
            $sop->update($data['sop']);
            $this->syncNested($sop, $data);
        });

        return redirect()->route('sopbuilder.sops.show', $sop)->with('message', __('SOP updated.'));
    }

    public function destroy(Sop $sop)
    {
        $sop->delete();

        return redirect()->route('sopbuilder.sops.index')->with('message', __('SOP deleted.'));
    }

    public function publish(Sop $sop)
    {
        $sop->update([
            'status' => 'published',
            'published_at' => now(),
            'version' => $sop->version + 1,
        ]);

        return redirect()->route('sopbuilder.sops.show', $sop)->with('message', __('SOP published.'));
    }

    public function duplicate(Sop $sop)
    {
        $copy = $sop->replicate();
        $copy->title = $sop->title.' '.__('(Copy)');
        $copy->slug = $this->uniqueSlug($copy->title);
        $copy->status = 'draft';
        $copy->published_at = null;
        $copy->version = 1;
        $copy->save();

        foreach ($sop->steps as $step) {
            $newStep = $step->replicate();
            $newStep->sop_id = $copy->id;
            $newStep->save();
        }

        foreach ($sop->checklistItems as $item) {
            $newItem = $item->replicate();
            $newItem->sop_id = $copy->id;
            $newItem->save();
        }

        foreach ($sop->quizzes as $quiz) {
            $newQuiz = $quiz->replicate();
            $newQuiz->sop_id = $copy->id;
            $newQuiz->save();
        }

        return redirect()->route('sopbuilder.sops.edit', $copy)->with('message', __('SOP duplicated.'));
    }

    private function validateSop(Request $request, ?Sop $sop = null): array
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:sop_categories,id',
            'description' => 'nullable|string',
            'why' => 'nullable|string',
            'who' => 'nullable|string',
            'when' => 'nullable|string',
            'input' => 'nullable|string',
            'output' => 'nullable|string',
            'risks' => 'nullable|string',
            'tools' => 'nullable|string',
            'status' => 'nullable|string|in:draft,published,archived',
        ]);

        $sopData = $request->only([
            'title', 'category_id', 'description', 'why', 'who', 'when',
            'input', 'output', 'risks', 'tools', 'status',
        ]);

        $sopData['slug'] = $this->uniqueSlug($request->title, $sop?->id);

        if ($request->input('status') === 'published' && (! $sop || $sop->status !== 'published')) {
            $sopData['published_at'] = now();
            $sopData['version'] = ($sop?->version ?? 0) + 1;
        }

        return [
            'sop' => $sopData,
            'steps' => $request->input('steps', []),
            'checklist' => $request->input('checklist', []),
            'quizzes' => $request->input('quizzes', []),
        ];
    }

    private function syncNested(Sop $sop, array $data): void
    {
        $existingStepIds = [];
        foreach ($data['steps'] as $index => $stepData) {
            if (empty($stepData['title'])) {
                continue;
            }
            $step = $sop->steps()->updateOrCreate(
                ['id' => $stepData['id'] ?? null],
                [
                    'sort_order' => $index,
                    'title' => $stepData['title'],
                    'description' => $stepData['description'] ?? null,
                ]
            );
            $existingStepIds[] = $step->id;

            if (! empty($stepData['checklist'])) {
                foreach ($stepData['checklist'] as $ci => $itemData) {
                    if (empty($itemData['text'])) {
                        continue;
                    }
                    $sop->checklistItems()->updateOrCreate(
                        ['id' => $itemData['id'] ?? null],
                        [
                            'step_id' => $step->id,
                            'sort_order' => $ci,
                            'text' => $itemData['text'],
                            'is_required' => $itemData['is_required'] ?? true,
                        ]
                    );
                }
            }
        }
        $sop->steps()->whereNotIn('id', $existingStepIds)->delete();

        $existingChecklistIds = [];
        foreach ($data['checklist'] as $index => $itemData) {
            if (empty($itemData['text'])) {
                continue;
            }
            $item = $sop->checklistItems()->updateOrCreate(
                ['id' => $itemData['id'] ?? null],
                [
                    'step_id' => null,
                    'sort_order' => $index,
                    'text' => $itemData['text'],
                    'is_required' => $itemData['is_required'] ?? true,
                ]
            );
            $existingChecklistIds[] = $item->id;
        }
        $sop->checklistItems()->whereNull('step_id')->whereNotIn('id', $existingChecklistIds)->delete();

        $existingQuizIds = [];
        foreach ($data['quizzes'] as $index => $quizData) {
            if (empty($quizData['question'])) {
                continue;
            }
            $quiz = $sop->quizzes()->updateOrCreate(
                ['id' => $quizData['id'] ?? null],
                [
                    'sort_order' => $index,
                    'question' => $quizData['question'],
                    'answer_type' => $quizData['answer_type'] ?? 'single',
                    'options' => ! empty($quizData['options']) ? explode("\n", $quizData['options']) : null,
                    'correct_answer' => $quizData['correct_answer'] ?? null,
                    'explanation' => $quizData['explanation'] ?? null,
                ]
            );
            $existingQuizIds[] = $quiz->id;
        }
        $sop->quizzes()->whereNotIn('id', $existingQuizIds)->delete();
    }

    private function uniqueSlug(string $title, ?int $excludeId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 1;

        while (Sop::where('slug', $slug)->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
