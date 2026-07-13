<?php

namespace Modules\LeadQuality\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LeadQuality\Models\Contact;
use Modules\LeadQuality\Models\Sequence;

class SequenceController
{
    public function index(): View
    {
        return view('leadquality::sequences.index', [
            'sequences' => Sequence::query()->withCount('contacts')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Sequence::create([
            'name' => $validated['name'],
            'team_id' => auth()->user()->current_team_id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('leadquality.sequences.index')->with('success', __('Sequence created!'));
    }

    public function show(Sequence $sequence): View
    {
        $sequence->load(['steps', 'contacts']);

        return view('leadquality::sequences.show', compact('sequence'));
    }

    public function update(Request $request, Sequence $sequence): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $sequence->update([
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('leadquality.sequences.show', $sequence)->with('success', __('Sequence updated!'));
    }

    public function destroy(Sequence $sequence): RedirectResponse
    {
        $sequence->delete();

        return redirect()->route('leadquality.sequences.index')->with('success', __('Sequence deleted.'));
    }

    public function storeStep(Request $request, Sequence $sequence): RedirectResponse
    {
        $validated = $request->validate([
            'delay_days' => 'required|integer|min:0',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $order = (int) ($sequence->steps()->max('order') ?? 0) + 1;

        $sequence->steps()->create($validated + [
            'team_id' => auth()->user()->current_team_id,
            'order' => $order,
        ]);

        return redirect()->route('leadquality.sequences.show', $sequence)->with('success', __('Step added!'));
    }

    public function enroll(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:leadquality_contacts,id',
            'sequence_id' => 'required|exists:leadquality_sequences,id',
        ]);

        $contact = Contact::query()->findOrFail($validated['contact_id']);
        $sequence = Sequence::query()->findOrFail($validated['sequence_id']);
        $firstStep = $sequence->steps()->orderBy('order')->first();

        if (! $contact->sequences()->where('leadquality_sequences.id', $sequence->id)->exists()) {
            $contact->sequences()->attach($sequence->id, [
                'team_id' => auth()->user()->current_team_id,
                'current_step_id' => $firstStep?->id,
                'next_run_at' => $firstStep ? now()->addDays($firstStep->delay_days) : null,
                'status' => 'active',
            ]);

            return redirect()->back()->with('success', __('Contact enrolled in sequence!'));
        }

        return redirect()->back()->with('error', __('Contact is already in this sequence.'));
    }
}
