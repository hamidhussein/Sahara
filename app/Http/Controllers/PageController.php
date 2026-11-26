<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    /** Shows a fixed page by slug; the route decides which slug (privacy, refund-policy, terms). */
    public function __invoke(string $slug): View
    {
        $page = Page::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return view('page', ['page' => $page]);
    }
}
