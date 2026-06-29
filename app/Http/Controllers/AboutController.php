<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/** The public About page: mission, vision, values and approach. */
class AboutController extends Controller
{
    public function show(): View
    {
        return view('public.about');
    }
}
