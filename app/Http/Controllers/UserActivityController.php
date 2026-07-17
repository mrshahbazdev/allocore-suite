<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class UserActivityController extends Controller
{
    public function index(Request $request): View
    {
        $activities = $request->user()
            ->activities()
            ->latest()
            ->paginate(25);

        return view('profile.activity', compact('activities'));
    }
}
