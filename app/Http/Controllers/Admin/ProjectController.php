<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Projects/Index', [
            'projects' => Project::with('skills')->orderBy('sort_order')->orderBy('created_at', 'desc')->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Projects/Create', [
            'availableSkills' => Skill::orderBy('category')->orderBy('name')->get(['id', 'name', 'category']),
        ]);
    }

    public function edit(Project $project)
    {
        $project->load('skills');

        return Inertia::render('Admin/Projects/Edit', [
            'project' => $project,
            'availableSkills' => Skill::orderBy('category')->orderBy('name')->get(['id', 'name', 'category']),
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
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        $skillIds = $validated['skills'] ?? [];
        unset($validated['skills']);

        $project = Project::create($validated);
        $project->skills()->sync($skillIds);

        return redirect()->route('admin.projects.index')->with('success', 'Project created successfully.');
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
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
        ]);

        if ($project->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $skillIds = $validated['skills'] ?? [];
        unset($validated['skills']);

        $project->update($validated);
        $project->skills()->sync($skillIds);

        return redirect()->route('admin.projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->back();
    }
}
