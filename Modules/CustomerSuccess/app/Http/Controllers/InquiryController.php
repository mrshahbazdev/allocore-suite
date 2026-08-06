<?php

namespace Modules\CustomerSuccess\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CustomerSuccess\Models\Inquiry;
use Modules\CustomerSuccess\Services\CustomerSuccessAssistant;

class InquiryController extends Controller
{
    public function index()
    {
        $inquiries = Inquiry::latest()->paginate(20);

        return view('customersuccess::inquiries.index', compact('inquiries'));
    }

    public function create()
    {
        return view('customersuccess::inquiries.form', ['inquiry' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
            'module_key' => ['nullable', 'string', 'max:50'],
        ]);

        $assistant = app(CustomerSuccessAssistant::class);
        $result = $assistant->ask($request->user(), $data['question'], $data['module_key'] ?? null);

        $inquiry = Inquiry::create([
            'question' => $data['question'],
            'module_key' => $data['module_key'] ?? null,
            'answer' => $result['answer'] ?? null,
            'problem' => $result['problem'] ?? null,
            'root_cause' => $result['root_cause'] ?? null,
            'consequences' => $result['consequences'] ?? null,
            'recommended_actions' => $result['recommended_actions'] ?? null,
            'priority' => $result['priority'] ?? null,
            'estimated_cost' => $result['estimated_cost'] ?? null,
            'expected_benefit' => $result['expected_benefit'] ?? null,
            'sources' => $result['sources'] ?? [],
        ]);

        return redirect()->route('customersuccess.inquiries.show', $inquiry)->with('message', __('Question answered.'));
    }

    public function show(Inquiry $inquiry)
    {
        return view('customersuccess::inquiries.show', compact('inquiry'));
    }

    public function destroy(Inquiry $inquiry)
    {
        $inquiry->delete();

        return redirect()->route('customersuccess.inquiries.index')->with('message', __('Inquiry deleted.'));
    }
}
