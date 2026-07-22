<?php

namespace App\Http\Controllers;

use App\Support\WorkspaceAnalyzer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WorkspaceController extends Controller
{
    public function __construct(protected WorkspaceAnalyzer $analyzer) {}

    public function __invoke(Request $request)
    {
        $data = $this->analyzer->analyze($request->user());

        return view('workspace.index', $data);
    }
}
