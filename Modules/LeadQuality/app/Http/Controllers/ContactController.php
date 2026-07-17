<?php

namespace Modules\LeadQuality\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LeadQuality\Jobs\AnalyzeContactsJob;
use Modules\LeadQuality\Models\Contact;
use Modules\LeadQuality\Models\OutreachTemplate;
use Modules\LeadQuality\Models\Sequence;
use Modules\LeadQuality\Services\AiLeadScoringService;
use Modules\LeadQuality\Services\CsvImportService;
use Modules\LeadQuality\Services\LeadScoreEngine;
use Modules\LeadQuality\Services\TemplateService;

class ContactController
{
    public function index(): View
    {
        $contacts = Contact::query()->latest()->get();

        $contacts->each(function (Contact $contact): void {
            $contact->analysis = app(LeadScoreEngine::class)->calculateScore($contact);
        });

        return view('leadquality::contacts.index', compact('contacts'));
    }

    public function create(): View
    {
        return view('leadquality::contacts.form', [
            'contact' => new Contact,
            'templates' => collect(),
            'sequences' => collect(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'source' => 'nullable|string|max:255',
            'priority' => 'nullable|integer|min:1|max:5',
        ]);

        Contact::create(array_merge($validated, [
            'user_id' => auth()->id(),
            'team_id' => auth()->user()->current_team_id,
            'source' => $validated['source'] ?? 'Manual Entry',
        ]));

        return redirect()->route('leadquality.contacts.index')->with('success', __('Contact created successfully!'));
    }

    public function show(Contact $contact, TemplateService $templateService, AiLeadScoringService $aiService): View
    {
        $contact->analysis = app(LeadScoreEngine::class)->calculateScore($contact);
        $contact->load(['activities', 'sequences.steps']);

        $templates = OutreachTemplate::query()
            ->get()
            ->map(function (OutreachTemplate $template) use ($contact, $templateService) {
                $template->merged_content = $templateService->merge($template->content, $contact);

                return $template;
            });

        return view('leadquality::contacts.show', [
            'contact' => $contact,
            'templates' => $templates,
            'aiInsights' => $aiService->analyze($contact),
            'sequences' => Sequence::query()->where('is_active', true)->get(),
        ]);
    }

    public function edit(Contact $contact): View
    {
        return view('leadquality::contacts.form', [
            'contact' => $contact,
            'templates' => collect(),
            'sequences' => collect(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'source' => 'nullable|string|max:255',
            'priority' => 'nullable|integer|min:1|max:5',
        ]);

        $contact->update($validated);

        return redirect()->route('leadquality.contacts.show', $contact)->with('success', __('Contact updated successfully!'));
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('leadquality.contacts.index')->with('success', __('Contact deleted successfully!'));
    }

    public function import(Request $request, CsvImportService $importService): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $results = $importService->import(
            $request->file('csv_file')->getRealPath(),
            auth()->id(),
            auth()->user()->current_team_id
        );

        if (! empty($results['errors'])) {
            return redirect()->route('leadquality.contacts.index')
                ->with('error', __('Import partially failed: ').implode(', ', array_slice($results['errors'], 0, 3)));
        }

        return redirect()->route('leadquality.contacts.index')
            ->with('success', __('Imported :count contacts successfully!', ['count' => $results['success']]));
    }

    public function analyzeAi(Contact $contact, AiLeadScoringService $aiScoringService): RedirectResponse
    {
        $analysis = $aiScoringService->analyze($contact);

        $contact->update([
            'ai_high_probability' => $analysis['high_probability'],
            'score' => $analysis['score'],
        ]);

        return redirect()->route('leadquality.contacts.show', $contact)
            ->with('success', __('AI Analysis complete!'))
            ->with('ai_insights', $analysis['insights']);
    }

    public function analyzeAll(): RedirectResponse
    {
        $teamId = auth()->user()->current_team_id;
        AnalyzeContactsJob::dispatch($teamId);

        return redirect()->route('leadquality.contacts.index')->with('success', __('AI analysis started for all contacts.'));
    }
}
