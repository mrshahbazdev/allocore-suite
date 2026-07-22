<?php

namespace Modules\DentalTrack\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\DentalTrack\Models\Company;
use Modules\DentalTrack\Models\Lab;
use Modules\DentalTrack\Models\Order;
use Modules\DentalTrack\Models\ProductType;
use Modules\DentalTrack\Services\PredictionService;
use Modules\DentalTrack\Services\QrCodeService;
use Modules\DentalTrack\Services\StickerPdfService;

class OrderController extends Controller
{
    public function __construct(
        private readonly PredictionService $predictionService,
        private readonly QrCodeService $qrCodeService,
        private readonly StickerPdfService $stickerPdfService,
    ) {}

    public function index(Request $request): View
    {
        $query = Order::with(['company', 'lab', 'productType'])->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('patient_ref', 'like', "%{$request->search}%")
                    ->orWhere('doctor_name', 'like', "%{$request->search}%")
                    ->orWhere('tracking_code', 'like', "%{$request->search}%");
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('dentaltrack::admin.orders.index', compact('orders'));
    }

    public function create(): View
    {
        $companies = Company::where('is_active', true)->get();
        $labs = Lab::where('is_active', true)->get();
        $productTypes = ProductType::where('is_active', true)->get();

        return view('dentaltrack::admin.orders.form', compact('companies', 'labs', 'productTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $order = Order::create($validated);

        $this->createSteps($order);

        $this->predictionService->predictCompletion($order);

        return redirect()->route('dentaltrack.admin.orders.index')->with('success', __('Order created.'));
    }

    public function show(Order $order): View
    {
        $order->load(['company', 'lab', 'productType', 'steps.scanEvents', 'scanEvents.user', 'scanEvents.workstation', 'predictions', 'reworkEvents']);

        return view('dentaltrack::admin.orders.show', compact('order'));
    }

    public function edit(Order $order): View
    {
        $order->load('steps');
        $companies = Company::where('is_active', true)->get();
        $labs = Lab::where('is_active', true)->get();
        $productTypes = ProductType::where('is_active', true)->get();

        return view('dentaltrack::admin.orders.form', compact('order', 'companies', 'labs', 'productTypes'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $order->update($validated);

        return redirect()->route('dentaltrack.admin.orders.index')->with('success', __('Order updated.'));
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return back()->with('success', __('Order deleted.'));
    }

    public function sticker(Order $order)
    {
        return $this->stickerPdfService->generateOrderSticker($order);
    }

    public function printStickers(Request $request)
    {
        $ids = $request->input('ids', []);
        $orders = Order::whereIn('id', $ids)->get();

        if ($orders->isEmpty()) {
            return back()->withErrors(['ids' => __('No orders selected.')]);
        }

        return $this->stickerPdfService->generateBatchOrderStickers($orders);
    }

    private function rules(): array
    {
        return [
            'dentaltrack_company_id' => 'required|exists:dentaltrack_companies,id',
            'dentaltrack_lab_id' => 'required|exists:dentaltrack_labs,id',
            'dentaltrack_product_type_id' => 'required|exists:dentaltrack_product_types,id',
            'patient_ref' => 'nullable|string|max:255',
            'doctor_name' => 'nullable|string|max:255',
            'priority' => 'required|in:low,normal,high,urgent',
            'due_date' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,completed,cancelled,on_hold',
            'notes' => 'nullable|string',
        ];
    }

    private function createSteps(Order $order): void
    {
        $templates = $order->productType->processTemplates;

        if ($templates->isEmpty()) {
            return;
        }

        foreach ($templates as $index => $template) {
            $order->steps()->create([
                'dentaltrack_process_template_id' => $template->id,
                'sort_order' => $index + 1,
                'step_name' => $template->step_name,
            ]);
        }
    }
}
