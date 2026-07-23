<?php

namespace App\Http\Controllers;

use App\Support\UsageAnalytics;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UsageAnalyticsController extends Controller
{
    public function __invoke(Request $request, UsageAnalytics $analytics)
    {
        return view('usage.index', ['analytics' => $analytics->forUser($request->user())]);
    }
}
