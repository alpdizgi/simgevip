<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('store.about');
    }

    public function show($slug): View
    {
        $page = \App\Models\Page::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        return view('store.page', compact('page'));
    }
}
