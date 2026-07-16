<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialPlatform\Models\Analysis;

class FinancialController extends Controller
{
    public function index(Request $request)
    {
        $analyses = Analysis::withoutGlobalScope('current_team')
            ->with(['company', 'user', 'team'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%')
                    ->orWhereHas('company', fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'))
                    ->orWhereHas('team', fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'total' => Analysis::withoutGlobalScope('current_team')->count(),
            'complete' => Analysis::withoutGlobalScope('current_team')->where('status', 'complete')->count(),
            'average_score' => Analysis::withoutGlobalScope('current_team')->whereNotNull('total_score')->avg('total_score') ?? 0,
        ];

        return view('admin.financial.index', compact('analyses', 'summary'));
    }
}
