<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExperienceController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Experiences/Index', [
            'experiences' => Experience::orderBy('sort_order')->orderBy('start_date', 'desc')->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Experiences/Create');
    }

    public function edit(Experience $experience)
    {
        return Inertia::render('Admin/Experiences/Edit', [
            'experience' => $experience,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'boolean',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'sort_order' => 'integer',
        ]);

        Experience::create($validated);

        return redirect()->route('admin.experiences.index')->with('success', 'Experience created successfully.');
    }

    public function update(Request $request, Experience $experience)
    {
        $validated = $request->validate([
            'company' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'boolean',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'sort_order' => 'integer',
        ]);

        $experience->update($validated);

        return redirect()->route('admin.experiences.index')->with('success', 'Experience updated successfully.');
    }

    public function destroy(Experience $experience)
    {
        $experience->delete();
        return redirect()->back();
    }
}
