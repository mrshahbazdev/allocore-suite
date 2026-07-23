<?php

namespace App\Http\Controllers;

use App\Support\ModuleRecommender;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RecommendationController extends Controller
{
    public function __invoke(Request $request, ModuleRecommender $recommender)
    {
        $data = $recommender->forUser($request->user());

        return view('recommendations.index', [
            'similar' => collect($data['similar']),
            'combos' => collect($data['combos']),
        ]);
    }
}
