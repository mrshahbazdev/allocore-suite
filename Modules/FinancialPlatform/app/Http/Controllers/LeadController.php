<?php

namespace Modules\FinancialPlatform\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\FinancialPlatform\Models\Company;
use Modules\FinancialPlatform\Models\Lead;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('company_name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $leads = $query->with('company')->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => Lead::query()->count(),
            'new' => Lead::query()->where('status', 'new')->count(),
            'contacted' => Lead::query()->where('status', 'contacted')->count(),
            'qualified' => Lead::query()->where('status', 'qualified')->count(),
            'transferred' => Lead::query()->where('transferred_to_leados', true)->count(),
        ];

        return view('financialplatform::leads.index', compact('leads', 'stats'));
    }

    public function create()
    {
        $companies = Company::query()->orderBy('name')->get();

        return view('financialplatform::leads.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company_id' => 'nullable|exists:financial_companies,id',
            'company_name' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'linkedin' => 'nullable|url|max:500',
            'website' => 'nullable|url|max:500',
            'source' => 'nullable|string|max:100',
            'status' => 'nullable|in:new,contacted,qualified,proposal,won,lost',
            'priority' => 'nullable|in:low,medium,high,critical',
            'industry' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:5000',
        ]);

        $validated['user_id'] = auth()->id();

        Lead::create($validated);

        return redirect()->route('leads.index')->with('success', 'Lead erfolgreich erstellt.');
    }

    public function show(Lead $lead)
    {
        $lead->load('company', 'paypalTransactions');

        return view('financialplatform::leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $companies = Company::query()->orderBy('name')->get();

        return view('financialplatform::leads.edit', compact('lead', 'companies'));
    }

    public function update(Request $request, Lead $lead)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company_id' => 'nullable|exists:financial_companies,id',
            'company_name' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'linkedin' => 'nullable|url|max:500',
            'website' => 'nullable|url|max:500',
            'source' => 'nullable|string|max:100',
            'status' => 'nullable|in:new,contacted,qualified,proposal,won,lost',
            'priority' => 'nullable|in:low,medium,high,critical',
            'industry' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:5000',
        ]);

        $lead->update($validated);

        return redirect()->route('leads.show', $lead)->with('success', 'Lead aktualisiert.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead gelöscht.');
    }

    public function transferToLeadOs(Request $request)
    {
        $request->validate([
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'exists:financial_leads,id',
            'leados_api_url' => 'required|url',
            'leados_token' => 'required|string',
        ]);

        $leads = Lead::query()
            ->whereIn('id', $request->lead_ids)
            ->where('transferred_to_leados', false)
            ->get();

        if ($leads->isEmpty()) {
            return back()->with('error', 'Keine übertragbaren Leads ausgewählt.');
        }

        $transferred = 0;
        $errors = [];

        foreach ($leads as $lead) {
            try {
                $response = Http::withToken($request->leados_token)
                    ->timeout(15)
                    ->post(rtrim($request->leados_api_url, '/').'/api/leads/import', [
                        'name' => $lead->name,
                        'email' => $lead->email,
                        'company' => $lead->company_name ?? $lead->company?->name,
                        'position' => $lead->position,
                        'linkedin' => $lead->linkedin,
                    ]);

                if ($response->successful()) {
                    $lead->update([
                        'transferred_to_leados' => true,
                        'transferred_at' => now(),
                    ]);
                    $transferred++;
                } else {
                    $errors[] = "{$lead->name}: ".($response->json('message') ?? 'API-Fehler');
                }
            } catch (\Exception $e) {
                $errors[] = "{$lead->name}: Verbindungsfehler";
            }
        }

        $message = "{$transferred} Lead(s) erfolgreich an LeadOS übertragen.";
        if (! empty($errors)) {
            $message .= ' Fehler: '.implode(', ', $errors);
        }

        return back()->with($errors ? 'error' : 'success', $message);
    }

    public function exportCsv(Request $request)
    {
        $leads = Lead::query()
            ->with('company')
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads-export-'.date('Y-m-d').'.csv"',
        ];

        $callback = function () use ($leads) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'E-Mail', 'Telefon', 'Unternehmen', 'Position', 'Status', 'Priorität', 'Quelle', 'Budget', 'LeadOS', 'Erstellt']);

            foreach ($leads as $lead) {
                fputcsv($out, [
                    $lead->name,
                    $lead->email,
                    $lead->phone,
                    $lead->company_name ?? $lead->company?->name ?? '',
                    $lead->position,
                    $lead->status,
                    $lead->priority,
                    $lead->source,
                    $lead->budget,
                    $lead->transferred_to_leados ? 'Ja' : 'Nein',
                    $lead->created_at?->format('d.m.Y'),
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
