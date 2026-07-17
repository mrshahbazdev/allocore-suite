<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CookieConsentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['consent' => 'required|in:all,necessary']);

        return back()->withCookie(cookie('cookie_consent', $request->input('consent'), 60 * 24 * 365));
    }
}
