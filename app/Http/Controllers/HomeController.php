<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;

class HomeController extends Controller
{
    public function __invoke()
    {
        $blocks = SiteSetting::value('landing_blocks', []);

        if (empty($blocks)) {
            return view('welcome');
        }

        return view('home', compact('blocks'));
    }
}
