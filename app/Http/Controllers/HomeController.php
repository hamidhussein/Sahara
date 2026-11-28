<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\Cause;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Program;
use App\Models\Project;
use App\Settings\HomeSettings;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Completed projects in the home page carousel. */
    public const CAROUSEL_LIMIT = 8;

    public function __invoke(HomeSettings $home): View
    {
        return view('pages.home', [
            'home' => $home,
            'appeals' => Cause::active()->where('is_appeal', true)->withPaidTotal()->with(['program', 'media'])->limit(3)->get(),
            'programs' => Program::published()->ordered()->get(),
            // Proof of impact: finished work, those marked "Show on the home page" and those
            // with photos first, so the carousel opens on a real picture.
            'projects' => Project::published()
                ->where('status', ProjectStatus::Completed)
                ->whereHas('program', fn ($q) => $q->published())
                ->with(['program', 'media'])
                ->orderByDesc('is_featured')
                ->orderByRaw(
                    'exists (select 1 from media where media.model_type = ? and media.model_id = projects.id and media.collection_name = ?) desc',
                    [Project::class, 'cover']
                )
                ->latest('completed_on')->latest()
                ->limit(self::CAROUSEL_LIMIT)
                ->get(),
            'posts' => Post::published()->with('media')->latest('published_at')->limit(3)->get(),
            'partners' => Partner::published()->with('media')->get(),
        ]);
    }
}
