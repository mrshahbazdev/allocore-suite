<?php

namespace Modules\NurDu\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class NurDuController extends Controller
{
    public function index(): View
    {
        return view('nurdu::index');
    }
}
