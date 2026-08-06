<?php

namespace Modules\AuditIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AuditIntelligence\Models\Finding;
use Modules\AuditIntelligence\Models\Upsell;

class UpsellController extends Controller
{
    public function create(Finding $finding)
    {
        return view('auditintelligence::upsells.form', ['finding' => $finding, 'upsell' => null]);
    }

    public function store(Request $request, Finding $finding)
    {
        $data = $this->validateUpsell($request);
        $finding->upsells()->create($data['upsell']);

        return redirect()->route('auditintelligence.findings.show', $finding)->with('message', __('Upsell created.'));
    }

    public function edit(Finding $finding, Upsell $upsell)
    {
        return view('auditintelligence::upsells.form', compact('finding', 'upsell'));
    }

    public function update(Request $request, Finding $finding, Upsell $upsell)
    {
        $data = $this->validateUpsell($request);
        $upsell->update($data['upsell']);

        return redirect()->route('auditintelligence.findings.show', $finding)->with('message', __('Upsell updated.'));
    }

    public function destroy(Finding $finding, Upsell $upsell)
    {
        $upsell->delete();

        return redirect()->route('auditintelligence.findings.show', $finding)->with('message', __('Upsell deleted.'));
    }

    protected function validateUpsell(Request $request): array
    {
        return $request->validate([
            'upsell.type' => ['required', 'in:training,consultant,module,provider'],
            'upsell.name' => ['required', 'string', 'max:255'],
            'upsell.description' => ['nullable', 'string'],
            'upsell.link' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
