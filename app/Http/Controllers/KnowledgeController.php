<?php

namespace App\Http\Controllers;

use App\Models\GlossaryTerm;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class KnowledgeController extends Controller
{
    public function index(Request $request): View
    {
        $letter = strtoupper($request->input('letter', ''));
        $query = GlossaryTerm::published()->orderBy('term', 'asc');

        if ($letter && ctype_alpha($letter)) {
            $query->where('term', 'like', $letter.'%');
        }

        $terms = $query->get()
            ->groupBy(fn ($term) => $term->category ?: __('General'))
            ->sortKeys();

        $availableLetters = GlossaryTerm::published()
            ->get(['term'])
            ->map(fn ($item) => strtoupper(substr($item->term, 0, 1)))
            ->filter(fn ($l) => preg_match('/^[A-ZÄÖÜ]$/i', $l))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('knowledge.index', compact('terms', 'letter', 'availableLetters'));
    }

    public function show(GlossaryTerm $knowledge): View
    {
        abort_unless($knowledge->is_published, 404);

        return view('knowledge.show', compact('knowledge'));
    }
}
