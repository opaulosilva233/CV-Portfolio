<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SkillController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Skill::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
        }

        $skills = $query->orderBy('sort_order')->orderBy('proficiency', 'desc')->get();

        return Inertia::render('Admin/Skills/Index', [
            'skills' => $skills,
            'filters' => [
                'search' => $search
            ]
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Skills/Create', [
            'availableProjects' => Project::orderBy('title')->get(['id', 'title']),
            'availableEducations' => Education::orderBy('institution')->get(['id', 'institution', 'degree']),
            'availableExperiences' => Experience::with('roles:id,experience_id,role')->orderBy('company')->get(['id', 'company']),
        ]);
    }

    public function edit(Skill $skill)
    {
        $skill->load(['projects:id,title', 'educations:id,institution,degree', 'experiences:id,company']);

        return Inertia::render('Admin/Skills/Edit', [
            'skill' => $skill,
            'availableProjects' => Project::orderBy('title')->get(['id', 'title']),
            'availableEducations' => Education::orderBy('institution')->get(['id', 'institution', 'degree']),
            'availableExperiences' => Experience::with('roles:id,experience_id,role')->orderBy('company')->get(['id', 'company']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'proficiency' => 'required|integer|min:1|max:5',
            'icon' => 'nullable|string',
            'sort_order' => 'integer',
            'projects' => 'nullable|array',
            'projects.*' => 'integer|exists:projects,id',
            'educations' => 'nullable|array',
            'educations.*' => 'integer|exists:education,id',
            'experiences' => 'nullable|array',
            'experiences.*' => 'integer|exists:experiences,id',
        ]);

        $projectIds = $validated['projects'] ?? [];
        $educationIds = $validated['educations'] ?? [];
        $experienceIds = $validated['experiences'] ?? [];
        unset($validated['projects'], $validated['educations'], $validated['experiences']);

        $skill = Skill::create($validated);
        $skill->projects()->sync($projectIds);
        $skill->educations()->sync($educationIds);
        $skill->experiences()->sync($experienceIds);

        return redirect()->route('admin.skills.index')->with('success', 'Skill created successfully.');
    }

    public function update(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'proficiency' => 'required|integer|min:1|max:5',
            'icon' => 'nullable|string',
            'sort_order' => 'integer',
            'projects' => 'nullable|array',
            'projects.*' => 'integer|exists:projects,id',
            'educations' => 'nullable|array',
            'educations.*' => 'integer|exists:education,id',
            'experiences' => 'nullable|array',
            'experiences.*' => 'integer|exists:experiences,id',
        ]);

        $projectIds = $validated['projects'] ?? [];
        $educationIds = $validated['educations'] ?? [];
        $experienceIds = $validated['experiences'] ?? [];
        unset($validated['projects'], $validated['educations'], $validated['experiences']);

        $skill->update($validated);
        $skill->projects()->sync($projectIds);
        $skill->educations()->sync($educationIds);
        $skill->experiences()->sync($experienceIds);

        return redirect()->route('admin.skills.index')->with('success', 'Skill updated successfully.');
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();
        return redirect()->back();
    }

    public function reorder(Request $request)
    {
        $orderedIds = $request->input('ids');

        foreach ($orderedIds as $index => $id) {
            Skill::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        Skill::whereIn('id', $ids)->delete();
        return redirect()->back()->with('success', 'Selected skills deleted successfully.');
    }
}
