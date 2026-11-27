<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /** The list page is the ProjectBrowser Livewire component; see projects/index. */
    public function index(): View
    {
        return view('projects.index');
    }

    public function show(Project $project): View
    {
        abort_unless($project->is_published && $project->program?->is_published, 404);

        $project->load(['program', 'updates', 'media']);

        return view('projects.show', [
            'project' => $project,
            'related' => Project::published()
                ->whereBelongsTo($project->program)
                ->whereKeyNot($project->getKey())
                ->with(['media', 'program'])
                ->latest()
                ->limit(3)
                ->get(),
        ]);
    }
}
