<?php

namespace App\Http\Controllers;

use App\Support\AiAdvisor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdvisorController extends Controller
{
    public function __construct(protected AiAdvisor $advisor) {}

    public function __invoke(Request $request)
    {
        $recommendations = $this->advisor->forUser($request->user());

        return view('advisor.index', compact('recommendations'));
    }
}
