<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\InvoiceMaker\Models\Payment;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::withoutGlobalScope('current_team')
            ->with(['invoice.client', 'team'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('reference', 'like', '%'.$request->search.'%')
                    ->orWhereHas('invoice', fn ($q) => $q->where('invoice_number', 'like', '%'.$request->search.'%'))
                    ->orWhereHas('invoice.client', fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function show(int $id)
    {
        $payment = Payment::withoutGlobalScope('current_team')
            ->with(['invoice.client', 'team'])
            ->findOrFail($id);

        return view('admin.payments.show', compact('payment'));
    }
}
