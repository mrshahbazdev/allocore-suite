<?php

namespace App\Http\Controllers;

use App\Models\GlossaryTerm;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class GlossaryController extends Controller
{
    public function index(): View
    {
        $terms = GlossaryTerm::where('is_published', true)
            ->orderBy('sort_order')
            ->latest()
            ->get()
            ->groupBy(fn ($term) => $term->category ?: __('General'));

        return view('glossary.index', compact('terms'));
    }

    public function show(GlossaryTerm $glossary): View
    {
        abort_unless($glossary->is_published, 404);

        return view('glossary.show', compact('glossary'));
    }
}
