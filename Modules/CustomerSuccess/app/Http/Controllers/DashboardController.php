<?php

namespace Modules\CustomerSuccess\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CustomerSuccess\Models\Inquiry;

class DashboardController extends Controller
{
    public function index()
    {
        return view('customersuccess::dashboard', [
            'recent' => Inquiry::latest()->take(5)->get(),
            'stats' => [
                'inquiries' => Inquiry::count(),
                'critical' => Inquiry::where('priority', 'critical')->count(),
                'high' => Inquiry::where('priority', 'high')->count(),
            ],
        ]);
    }
}
