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
        $letter = strtoupper($request->input('letter', ''));
        $query = GlossaryTerm::where('is_published', true)->orderBy('term', 'asc');

        if ($letter && ctype_alpha($letter)) {
            $query->where('term', 'like', $letter.'%');
        }

        $terms = $query->get()
            ->groupBy(fn ($term) => $term->category ?: __('General'))
            ->sortKeys();

        $availableLetters = GlossaryTerm::where('is_published', true)
            ->get(['term'])
            ->map(fn ($item) => strtoupper(substr($item->term, 0, 1)))
            ->filter(fn ($l) => preg_match('/^[A-ZÄÖÜ]$/i', $l))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('glossary.index', compact('terms', 'letter', 'availableLetters'));
    }

    public function show(GlossaryTerm $glossary): View
    {
        abort_unless($glossary->is_published, 404);

        return view('glossary.show', compact('glossary'));
    }
}
