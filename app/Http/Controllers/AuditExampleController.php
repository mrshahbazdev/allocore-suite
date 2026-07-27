<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controller;
use Modules\AuditPro\Support\Maturity;

class AuditExampleController extends Controller
{
    public function index()
    {
        $score = $this->sampleScore();

        return view('audit-example', compact('score'));
    }

    public function pdf()
    {
        $score = $this->sampleScore();

        return response()->streamDownload(
            fn () => print Pdf::loadView('audit-example-pdf', compact('score'))->output(),
            'allocore-audit-example.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }

    private function sampleScore(): object
    {
        $pillars = collect([
            ['name' => 'Revenue', 'score' => 58, 'maturity' => Maturity::label(2.9)],
            ['name' => 'Profit', 'score' => 44, 'maturity' => Maturity::label(2.2)],
            ['name' => 'Order', 'score' => 72, 'maturity' => Maturity::label(3.6)],
            ['name' => 'Influence', 'score' => 36, 'maturity' => Maturity::label(1.8)],
            ['name' => 'Legacy', 'score' => 66, 'maturity' => Maturity::label(3.3)],
        ]);

        $average = $pillars->avg('score');

        return (object) [
            'score' => round($average, 2),
            'maturity_level' => Maturity::label(($average / 100) * 5),
            'pillars' => $pillars->all(),
            'calculated_at' => now()->subDays(3),
        ];
    }
}
