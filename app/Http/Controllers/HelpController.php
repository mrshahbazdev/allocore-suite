<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class HelpController extends Controller
{
    public function index(): View
    {
        $articles = Page::ofType('help')
            ->published()
            ->ordered()
            ->with('translations')
            ->get()
            ->map(fn (Page $page) => [
                'page' => $page,
                'translation' => $page->translation() ?? $page->translations->first(),
            ])
            ->filter(fn ($item) => $item['translation'] !== null);

        return view('help.index', compact('articles'));
    }
}
