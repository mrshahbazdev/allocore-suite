<?php

namespace Modules\DentalTrack\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\DentalTrack\Models\Order;
use Modules\DentalTrack\Models\ScanEvent;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->input('from', now()->subDays(30)->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $orders = Order::whereBetween('created_at', [$from, $to.' 23:59:59'])->count();
        $completed = Order::where('status', 'completed')->whereBetween('completed_at', [$from, $to.' 23:59:59'])->count();
        $scans = ScanEvent::whereBetween('scanned_at', [$from, $to.' 23:59:59'])->count();

        $avgStepDuration = ScanEvent::where('event_type', 'complete')
            ->whereBetween('scanned_at', [$from, $to.' 23:59:59'])
            ->whereNotNull('duration_seconds')
            ->avg('duration_seconds');

        return view('dentaltrack::admin.reports.index', compact('from', 'to', 'orders', 'completed', 'scans', 'avgStepDuration'));
    }

    public function exportOrders(Request $request)
    {
        $from = $request->input('from', now()->subDays(30)->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $orders = Order::with(['company', 'lab', 'productType'])
            ->whereBetween('created_at', [$from, $to.' 23:59:59'])
            ->get();

        $headers = ['Order ID', 'Company', 'Lab', 'Product Type', 'Patient Ref', 'Doctor', 'Priority', 'Status', 'Due Date', 'Created At', 'Completed At', 'Progress %'];
        $callback = function () use ($orders, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->company?->name,
                    $order->lab?->name,
                    $order->productType?->name,
                    $order->patient_ref,
                    $order->doctor_name,
                    $order->priority->value,
                    $order->status->value,
                    $order->due_date?->format('Y-m-d'),
                    $order->created_at?->format('Y-m-d H:i'),
                    $order->completed_at?->format('Y-m-d H:i'),
                    $order->progressPercentage().'%',
                ]);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, 'orders.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportScans(Request $request)
    {
        $from = $request->input('from', now()->subDays(30)->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $events = ScanEvent::with(['order', 'workstation', 'user'])
            ->whereBetween('scanned_at', [$from, $to.' 23:59:59'])
            ->get();

        $headers = ['Event ID', 'Order ID', 'Step', 'Workstation', 'Technician', 'Event Type', 'Scanned At', 'Duration (min)'];
        $callback = function () use ($events, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($events as $event) {
                fputcsv($file, [
                    $event->id,
                    $event->order?->id,
                    $event->orderStep?->step_name,
                    $event->workstation?->name,
                    $event->user?->name,
                    $event->event_type->value,
                    $event->scanned_at?->format('Y-m-d H:i:s'),
                    $event->duration_seconds !== null ? round($event->duration_seconds / 60, 1) : '',
                ]);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, 'scan-events.csv', ['Content-Type' => 'text/csv']);
    }
}
