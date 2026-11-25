<?php

namespace App\Http\Controllers;

use App\Models\Cause;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Program;
use App\Models\Project;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.home', [
            'appeals' => Cause::active()->where('is_appeal', true)->withPaidTotal()->with('media')->limit(3)->get(),
            'programs' => Program::published()->ordered()->get(),
            'projects' => Project::published()
                ->whereHas('program', fn ($q) => $q->published())
                ->where('is_featured', true)
                ->with(['program', 'media'])
                ->latest('completed_on')->latest()
                ->limit(3)
                ->get(),
            'posts' => Post::published()->with('media')->latest('published_at')->limit(3)->get(),
            'partners' => Partner::published()->with('media')->get(),
        ]);
    }
}
