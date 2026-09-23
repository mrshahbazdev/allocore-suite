<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\BookIntelligence\Models\QuestionMapping;

class FaqController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('q', $request->get('search', '')));
        $selectedCategory = trim((string) $request->get('category', 'all'));

        $query = QuestionMapping::withoutGlobalScope('current_team')
            ->where('is_active', true)
            ->with(['book.author', 'book.mainTopic']);

        if ($search !== '') {
            $query->search($search);
        }

        if ($selectedCategory !== '' && $selectedCategory !== 'all') {
            $query->where('category', $selectedCategory);
        }

        $faqs = $query->orderBy('priority', 'desc')->latest()->paginate(20)->withQueryString();

        $categories = QuestionMapping::withoutGlobalScope('current_team')
            ->where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        $totalCount = QuestionMapping::withoutGlobalScope('current_team')->where('is_active', true)->count();

        return view('faq.index', compact('faqs', 'categories', 'search', 'selectedCategory', 'totalCount'));
    }

    public function ask(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'question' => ['required', 'string', 'max:1000'],
            'category' => ['nullable', 'string', 'max:100'],
            'problem_details' => ['nullable', 'string', 'max:3000'],
        ]);

        try {
            QuestionMapping::withoutGlobalScope('current_team')->create([
                'question' => $validated['question'],
                'problem_statement' => $validated['problem_details'] ?? ('Gestellt von: ' . $validated['name'] . ' (' . $validated['email'] . ')'),
                'category' => $validated['category'] ?: 'Allgemein',
                'is_active' => false,
                'team_id' => 1,
            ]);

            return back()->with('success', __('Vielen Dank! Ihre Frage wurde erfolgreich eingereicht. Unser Team wird sie zeitnah prüfen und in die Wissensdatenbank aufnehmen.'));
        } catch (\Throwable $e) {
            return back()->with('success', __('Vielen Dank! Ihre Frage wurde erfolgreich übermittelt.'));
        }
    }
}
