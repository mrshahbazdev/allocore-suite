<?php

namespace App\Http\Controllers;

use App\Support\LandingBlocks;

class HomeController extends Controller
{
    public function __invoke()
    {
        $blocks = LandingBlocks::forPublic();

        return view('home', compact('blocks'));
    }
}
