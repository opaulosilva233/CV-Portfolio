<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\Skill;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExperienceController extends Controller
{
    public function index()
    {
        $experiences = Experience::with(['roles' => function ($q) {
            $q->orderBy('start_date', 'desc');
        }, 'skills'])->get()->sortByDesc(function ($exp) {
            return $exp->roles->max('start_date');
        })->values();

        return Inertia::render('Admin/Experiences/Index', [
            'experiences' => $experiences
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Experiences/Create', [
            'availableSkills' => Skill::orderBy('category')->orderBy('name')->get(['id', 'name', 'category']),
        ]);
    }

    public function edit(Experience $experience)
    {
        $experience->load(['roles' => function ($query) {
            $query->orderBy('start_date', 'desc');
        }, 'skills']);

        return Inertia::render('Admin/Experiences/Edit', [
            'experience' => $experience,
            'availableSkills' => Skill::orderBy('category')->orderBy('name')->get(['id', 'name', 'category']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company' => 'required|string|max:255',
            'location' => 'nullable|string',
            'roles' => 'required|array|min:1',
            'roles.*.role' => 'required|string|max:255',
            'roles.*.start_date' => 'required|date',
            'roles.*.end_date' => 'nullable|date|after_or_equal:roles.*.start_date',
            'roles.*.is_current' => 'boolean',
            'roles.*.description' => 'nullable|string',
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
        ]);

        $skillIds = $validated['skills'] ?? [];

        $experience = Experience::create([
            'company' => $validated['company'],
            'location' => $validated['location'] ?? null,
        ]);

        foreach ($validated['roles'] as $roleData) {
            $experience->roles()->create($roleData);
        }

        $experience->skills()->sync($skillIds);

        return redirect()->route('admin.experiences.index')->with('success', 'Experience created successfully.');
    }

    public function update(Request $request, Experience $experience)
    {
        $validated = $request->validate([
            'company' => 'required|string|max:255',
            'location' => 'nullable|string',
            'roles' => 'required|array|min:1',
            'roles.*.role' => 'required|string|max:255',
            'roles.*.start_date' => 'required|date',
            'roles.*.end_date' => 'nullable|date|after_or_equal:roles.*.start_date',
            'roles.*.is_current' => 'boolean',
            'roles.*.description' => 'nullable|string',
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
        ]);

        $skillIds = $validated['skills'] ?? [];

        $experience->update([
            'company' => $validated['company'],
            'location' => $validated['location'] ?? null,
        ]);

        $experience->roles()->delete();
        foreach ($validated['roles'] as $roleData) {
            $experience->roles()->create($roleData);
        }

        $experience->skills()->sync($skillIds);

        return redirect()->route('admin.experiences.index')->with('success', 'Experience updated successfully.');
    }

    public function destroy(Experience $experience)
    {
        $experience->delete();
        return redirect()->back();
    }
}
