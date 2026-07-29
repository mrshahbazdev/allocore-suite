<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Support\LandingBlockDefaults;

class HomeController extends Controller
{
    public function __invoke()
    {
        $blocks = SiteSetting::value('landing_blocks', []);

        if (empty($blocks)) {
            $blocks = LandingBlockDefaults::blocks();
        }

        return view('home', compact('blocks'));
    }
}
