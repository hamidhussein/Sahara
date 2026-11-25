<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(): View
    {
        return view('programs.index', [
            'programs' => Program::published()->ordered()->with('media')->get(),
        ]);
    }

    public function show(Program $program): View
    {
        abort_unless($program->is_published, 404);

        return view('programs.show', [
            'program' => $program,
            'projects' => $program->projects()->published()->with(['media', 'program'])->latest('completed_on')->latest()->get(),
        ]);
    }
}
