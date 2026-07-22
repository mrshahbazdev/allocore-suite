<?php

namespace App\Http\Controllers;

use App\Support\GlobalSearch;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SearchController extends Controller
{
    public function __construct(protected GlobalSearch $search) {}

    public function __invoke(Request $request)
    {
        $query = $request->input('q', '');
        $results = $this->search->search($request->user(), $query);

        return view('search.index', compact('results', 'query'));
    }
}
