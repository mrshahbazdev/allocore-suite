<?php

namespace App\Http\Controllers;

use App\Models\CaseStudy;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class CaseStudyController extends Controller
{
    public function index(): View
    {
        $caseStudies = CaseStudy::where('is_published', true)
            ->orderBy('sort_order')
            ->latest()
            ->paginate(9);

        return view('case-studies.index', compact('caseStudies'));
    }

    public function show(CaseStudy $caseStudy): View
    {
        abort_unless($caseStudy->is_published, 404);

        return view('case-studies.show', compact('caseStudy'));
    }
}
