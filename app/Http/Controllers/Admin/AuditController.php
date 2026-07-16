<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AuditPro\Models\Audit;
use Modules\AuditPro\Models\AuditTemplate;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $audits = Audit::withoutGlobalScope('current_team')
            ->with(['template', 'creator', 'team'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('team', fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'))
                    ->orWhereHas('creator', fn ($q) => $q->where('name', 'like', '%'.$request->search.'%')->orWhere('email', 'like', '%'.$request->search.'%'));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $templates = AuditTemplate::withoutGlobalScope('current_team')->withCount('audits')->get();

        return view('admin.audits.index', compact('audits', 'templates'));
    }

    public function show(int $auditId)
    {
        $audit = Audit::withoutGlobalScope('current_team')
            ->with(['template.pillars', 'creator', 'team', 'results.pillar', 'answers.question'])
            ->findOrFail($auditId);

        return view('admin.audits.show', compact('audit'));
    }
}
