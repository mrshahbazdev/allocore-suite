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
            ['name' => 'Revenue', 'score' => 58],
            ['name' => 'Profit', 'score' => 44],
            ['name' => 'Order', 'score' => 72],
            ['name' => 'Influence', 'score' => 36],
            ['name' => 'Legacy', 'score' => 66],
        ])->map(fn ($p) => array_merge($p, ['maturity' => Maturity::label(($p['score'] / 100) * 4)]));

        $average = $pillars->avg('score');

        return (object) [
            'score' => round($average, 2),
            'maturity_level' => Maturity::label(($average / 100) * 4),
            'pillars' => $pillars->all(),
            'calculated_at' => now()->subDays(3),
        ];
    }
}
