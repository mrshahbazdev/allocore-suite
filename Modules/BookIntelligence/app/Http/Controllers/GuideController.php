<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;

class GuideController extends Controller
{
    public function __invoke()
    {
        return view('bookintelligence::guide');
    }
}
