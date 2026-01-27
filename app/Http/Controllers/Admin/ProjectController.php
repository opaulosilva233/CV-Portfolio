<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Projects/Index', [
            'projects' => Project::orderBy('sort_order')->orderBy('created_at', 'desc')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image_url' => 'nullable|string',
            'project_url' => 'nullable|url',
            'github_url' => 'nullable|url',
            'tech_stack' => 'nullable|array',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        Project::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image_url' => 'nullable|string',
            'project_url' => 'nullable|url',
            'github_url' => 'nullable|url',
            'tech_stack' => 'nullable|array',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($project->title !== $validated['title']) {
             $validated['slug'] = Str::slug($validated['title']);
        }

        $project->update($validated);

        return redirect()->back();
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->back();
    }
}
