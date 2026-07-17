<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\InvoiceMaker\Models\Invoice;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::withoutGlobalScope('current_team')
            ->with(['client', 'team'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('invoice_number', 'like', '%'.$request->search.'%')
                        ->orWhere('uuid', 'like', '%'.$request->search.'%')
                        ->orWhereHas('client', fn ($client) => $client->where('name', 'like', '%'.$request->search.'%'));
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $statuses = [Invoice::STATUS_DRAFT, Invoice::STATUS_SENT, Invoice::STATUS_PAID, Invoice::STATUS_OVERDUE, Invoice::STATUS_CANCELLED];

        return view('admin.invoices.index', compact('invoices', 'statuses'));
    }

    public function show(int $id)
    {
        $invoice = Invoice::withoutGlobalScope('current_team')
            ->with(['client', 'team', 'items', 'payments', 'template'])
            ->findOrFail($id);

        return view('admin.invoices.show', compact('invoice'));
    }
}
