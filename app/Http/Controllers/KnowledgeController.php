<?php

namespace App\Http\Controllers;

use App\Models\GlossaryTerm;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class KnowledgeController extends Controller
{
    public function index(): View
    {
        $terms = GlossaryTerm::published()
            ->orderBy('sort_order')
            ->latest()
            ->get()
            ->groupBy(fn ($term) => $term->category ?: __('General'));

        return view('knowledge.index', compact('terms'));
    }

    public function show(GlossaryTerm $knowledge): View
    {
        abort_unless($knowledge->is_published, 404);

        return view('knowledge.show', compact('knowledge'));
    }
}
