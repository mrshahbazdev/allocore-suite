<?php

namespace Modules\LeadQuality\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiagnosticController
{
    public function __invoke(): View
    {
        return view('leadquality::diagnostic.index');
    }

    public function store(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'q1' => 'required|numeric',
            'q2' => 'required|numeric',
            'q3' => 'required|numeric',
            'q4' => 'required|numeric',
            'q5' => 'required|numeric',
            'q6' => 'required|numeric',
            'q7' => 'required|numeric',
            'q8' => 'required|numeric',
            'q9' => 'required|numeric',
            'q10' => 'required|numeric',
        ]);

        $score = array_sum($validated);

        $risk = __('Low');
        $riskClass = 'bg-emerald-100 text-emerald-800';

        if ($score < 50) {
            $risk = __('High Lead Quality Risk');
            $riskClass = 'bg-rose-100 text-rose-800';
        } elseif ($score < 80) {
            $risk = __('Moderate Risk - Process Gaps Detected');
            $riskClass = 'bg-amber-100 text-amber-800';
        }

        return view('leadquality::diagnostic.result', compact('score', 'risk', 'riskClass'));
    }
}
