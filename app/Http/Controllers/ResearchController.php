<?php

namespace App\Http\Controllers;

use App\Models\ResearchProject;
use Illuminate\View\View;

/**
 * The public Research area: the network's projects, field studies, baselines
 * and evaluations. Read-only; only active and completed projects are shown.
 */
class ResearchController extends Controller
{
    public function index(): View
    {
        $projects = ResearchProject::public()
            ->with(['lead', 'topics', 'regions'])
            ->latest('updated_at')
            ->paginate(12);

        return view('public.research.index', ['projects' => $projects]);
    }

    public function show(ResearchProject $project): View
    {
        abort_unless(in_array($project->status, ['active', 'completed'], true), 404);

        $project->load(['lead', 'members', 'topics', 'regions']);
        $publications = $project->publications()->where('status', 'published')->latest('published_at')->get();

        return view('public.research.show', [
            'project' => $project,
            'publications' => $publications,
        ]);
    }
}
