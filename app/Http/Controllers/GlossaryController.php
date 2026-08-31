<?php

namespace App\Http\Controllers;

use App\Models\GlossaryTerm;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class GlossaryController extends Controller
{
    public function index(Request $request): View
    {
        $terms = GlossaryTerm::where('is_published', true)
            ->orderBy('sort_order')
            ->latest()
            ->get();

        $availableLetters = $terms
            ->pluck('term')
            ->map(fn ($term) => mb_strtoupper(mb_substr($term, 0, 1)))
            ->filter(fn ($letter) => preg_match('/^[A-Z]$/', $letter))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $letter = $request->get('letter');
        if ($letter && preg_match('/^[A-Z]$/i', $letter)) {
            $letter = mb_strtoupper($letter);
            $terms = $terms->filter(fn ($term) => mb_strtoupper(mb_substr($term->term, 0, 1)) === $letter)->values();
        } else {
            $letter = null;
        }

        $terms = $terms->groupBy(fn ($term) => $term->category ?: __('General'));

        return view('glossary.index', compact('terms', 'availableLetters', 'letter'));
    }

    public function show(GlossaryTerm $glossary): View
    {
        abort_unless($glossary->is_published, 404);

        return view('glossary.show', compact('glossary'));
    }
}
