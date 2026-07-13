<?php

namespace Modules\LeadQuality\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LeadQuality\Models\Contact;

class PipelineController
{
    public function __invoke(): View
    {
        $contacts = Contact::query()->get();

        return view('leadquality::pipeline.index', [
            'pipeline' => [
                'new' => $contacts->where('pipeline_stage', 'new')->values(),
                'contacted' => $contacts->where('pipeline_stage', 'contacted')->values(),
                'meeting_set' => $contacts->where('pipeline_stage', 'meeting_set')->values(),
                'won' => $contacts->where('pipeline_stage', 'won')->values(),
                'lost' => $contacts->where('pipeline_stage', 'lost')->values(),
            ],
        ]);
    }

    public function updateStage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:leadquality_contacts,id',
            'stage' => 'required|string|in:new,contacted,meeting_set,won,lost',
        ]);

        $contact = Contact::query()->findOrFail($validated['contact_id']);
        $contact->update(['pipeline_stage' => $validated['stage']]);

        return response()->json(['success' => true]);
    }
}
