<?php

namespace Modules\SweetSpot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\SweetSpot\Models\Customer;
use Modules\SweetSpot\Services\SweetSpotScoringService;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with('score')->latest()->paginate(20);

        return view('sweetspot::customers.index', compact('customers'));
    }

    public function create()
    {
        return view('sweetspot::customers.form', ['customer' => new Customer]);
    }

    public function store(Request $request, SweetSpotScoringService $service)
    {
        $data = $this->validateCustomer($request);
        $customer = Customer::create($data);

        $service->calculateAll($customer->team_id);

        return redirect()->route('sweetspot.customers.index')->with('success', __('Customer created and scores recalculated.'));
    }

    public function show(Customer $customer)
    {
        $customer->load('score');

        return view('sweetspot::customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('sweetspot::customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer, SweetSpotScoringService $service)
    {
        $data = $this->validateCustomer($request);
        $customer->update($data);

        $service->calculateAll($customer->team_id);

        return redirect()->route('sweetspot.customers.index')->with('success', __('Customer updated and scores recalculated.'));
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('sweetspot.customers.index')->with('success', __('Customer deleted.'));
    }

    protected function validateCustomer(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'company_size' => 'nullable|string|max:255',
            'revenue' => 'nullable|numeric|min:0',
            'profit_margin_eur' => 'nullable|numeric',
            'margin_percent' => 'nullable|numeric|min:0|max:100',
            'effort_hours' => 'nullable|numeric|min:0',
            'chemistry_score' => 'nullable|integer|min:1|max:5',
            'growth_score' => 'nullable|integer|min:1|max:5',
            'repeat_rate' => 'nullable|numeric|min:0|max:100',
            'recommendations' => 'nullable|integer|min:0',
            'payment_willingness' => 'nullable|integer|min:1|max:5',
            'notes' => 'nullable|string|max:5000',
        ]);
    }
}
