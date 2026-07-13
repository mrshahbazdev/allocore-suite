<?php

namespace Modules\FinancialPlatform\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FinancialPlatformController extends Controller
{
    public function index()
    {
        return view('financialplatform::index');
    }

    public function create()
    {
        return view('financialplatform::create');
    }

    public function store(Request $request) {}

    public function show($id)
    {
        return view('financialplatform::show');
    }

    public function edit($id)
    {
        return view('financialplatform::edit');
    }

    public function update(Request $request, $id) {}

    public function destroy($id) {}
}
