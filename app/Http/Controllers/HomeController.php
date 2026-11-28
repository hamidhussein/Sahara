<?php

namespace App\Http\Controllers;

use App\Models\Cause;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Program;
use App\Models\Project;
use App\Settings\HomeSettings;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(HomeSettings $home): View
    {
        $tierSlugs = collect($home->tiers)->pluck('cause')->filter()->unique()->values();

        return view('pages.home', [
            'home' => $home,
            'appeals' => Cause::active()->where('is_appeal', true)->withPaidTotal()->with(['program', 'media'])->limit(3)->get(),
            'programs' => Program::published()->ordered()->get(),
            'projects' => Project::published()
                ->whereHas('program', fn ($q) => $q->published())
                ->where('is_featured', true)
                ->with(['program', 'media'])
                ->orderByRaw(
                    'exists (select 1 from media where media.model_type = ? and media.model_id = projects.id and media.collection_name = ?) desc',
                    [Project::class, 'cover']
                )
                ->latest('completed_on')->latest()
                ->limit(3)
                ->get(),
            'tierCauses' => $tierSlugs->isNotEmpty()
                ? Cause::whereIn('slug', $tierSlugs)->pluck('name', 'slug')
                : collect(),
            'posts' => Post::published()->with('media')->latest('published_at')->limit(3)->get(),
            'partners' => Partner::published()->with('media')->get(),
        ]);
    }
}
