<?php

namespace Modules\DentalTrack\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\DentalTrack\Enums\WorkstationType;
use Modules\DentalTrack\Models\Order;
use Modules\DentalTrack\Models\Workstation;
use Modules\DentalTrack\Services\ScanService;

class ScanController extends Controller
{
    public function index(?string $uuid = null): View
    {
        $workstation = null;
        $order = null;

        if ($uuid !== null) {
            $workstation = Workstation::where('qr_code', $uuid)->where('is_active', true)->first();

            if ($workstation === null) {
                $order = Order::where('qr_code', $uuid)->first();
            }
        }

        if ($workstation !== null) {
            session(['dentaltrack_workstation_id' => $workstation->id]);
        }

        return view('dentaltrack::scan.index', compact('workstation', 'order'));
    }

    public function process(Request $request, ScanService $scanService): RedirectResponse|View
    {
        $data = $request->validate([
            'qr_data' => 'required|string',
            'workstation_id' => 'nullable|integer',
            'action' => 'nullable|string|in:start,pause,complete,transfer',
            'notes' => 'nullable|string',
        ]);

        $uuid = $this->extractUuid($data['qr_data']);

        if ($uuid === null) {
            return back()->withErrors(['qr_data' => __('Invalid QR code format.')]);
        }

        $workstation = Workstation::where('qr_code', $uuid)->where('is_active', true)->first();

        if ($workstation !== null) {
            session(['dentaltrack_workstation_id' => $workstation->id]);

            return redirect()->route('dentaltrack.scan.index', ['uuid' => $workstation->qr_code]);
        }

        $order = Order::where('qr_code', $uuid)->first();

        if ($order === null) {
            return back()->withErrors(['qr_data' => __('QR code not recognized.')]);
        }

        if (empty($data['action'])) {
            return view('dentaltrack::scan.index', [
                'order' => $order,
                'workstation' => session('dentaltrack_workstation_id') ? Workstation::find(session('dentaltrack_workstation_id')) : null,
            ]);
        }

        $workstationId = $data['workstation_id'] ?? session('dentaltrack_workstation_id');
        $workstation = $workstationId ? Workstation::find($workstationId) : null;

        if ($workstation === null) {
            return back()->withErrors(['workstation_id' => __('Please scan a workstation first.')]);
        }

        $user = auth()->user();
        $notes = $data['notes'] ?? null;

        try {
            match ($data['action']) {
                'start' => $scanService->startWork($order, $workstation, $user, $notes),
                'pause' => $scanService->pauseWork($order, $workstation, $user, $notes),
                'complete' => $scanService->completeWork($order, $workstation, $user, $notes),
                'transfer' => $workstation->type === WorkstationType::WaitingArea
                    ? $scanService->transferToWaiting($order, $workstation, $user, $notes)
                    : throw new \RuntimeException(__('Selected workstation is not a waiting area.')),
                default => throw new \InvalidArgumentException(__('Unknown action.')),
            };
        } catch (\RuntimeException $e) {
            return back()->withErrors(['qr_data' => $e->getMessage()]);
        }

        return redirect()->route('dentaltrack.scan.index')->with('success', __('Action completed successfully.'));
    }

    private function extractUuid(string $qrData): ?string
    {
        if (preg_match('/\/dentaltrack\/scan\/([A-Za-z0-9_-]+)$/', $qrData, $matches)) {
            return $matches[1];
        }

        if (str_starts_with($qrData, 'WS-') || str_starts_with($qrData, 'ORD-') || str_starts_with($qrData, 'WA-')) {
            return $qrData;
        }

        return null;
    }
}
