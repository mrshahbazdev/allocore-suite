<?php

namespace Modules\DentalTrack\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\DentalTrack\Models\Lab;
use Modules\DentalTrack\Models\Workstation;
use Modules\DentalTrack\Services\QrCodeService;
use Modules\DentalTrack\Services\StickerPdfService;

class WorkstationController extends Controller
{
    public function __construct(
        private readonly QrCodeService $qrCodeService,
        private readonly StickerPdfService $stickerPdfService,
    ) {}

    public function index(Request $request): View
    {
        $query = Workstation::with('lab')->orderByDesc('created_at');

        if ($request->lab_id) {
            $query->where('dentaltrack_lab_id', $request->lab_id);
        }

        $workstations = $query->paginate(20)->withQueryString();
        $labs = Lab::where('is_active', true)->get();

        return view('dentaltrack::admin.workstations.index', compact('workstations', 'labs'));
    }

    public function create(): View
    {
        $labs = Lab::where('is_active', true)->get();

        return view('dentaltrack::admin.workstations.form', compact('labs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Workstation::create($validated);

        return redirect()->route('dentaltrack.admin.workstations.index')->with('success', __('Workstation created.'));
    }

    public function edit(Workstation $workstation): View
    {
        $labs = Lab::where('is_active', true)->get();

        return view('dentaltrack::admin.workstations.form', compact('workstation', 'labs'));
    }

    public function update(Request $request, Workstation $workstation): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $workstation->update($validated);

        return redirect()->route('dentaltrack.admin.workstations.index')->with('success', __('Workstation updated.'));
    }

    public function destroy(Workstation $workstation): RedirectResponse
    {
        $workstation->delete();

        return back()->with('success', __('Workstation deleted.'));
    }

    public function sticker(Workstation $workstation)
    {
        return $this->stickerPdfService->generateWorkstationSticker($workstation);
    }

    private function rules(): array
    {
        return [
            'dentaltrack_lab_id' => 'required|exists:dentaltrack_labs,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:station,waiting_area',
            'is_active' => 'nullable|boolean',
        ];
    }
}
