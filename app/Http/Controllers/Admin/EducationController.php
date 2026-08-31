<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Education;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class EducationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Education::query()->select(['id', 'institution', 'degree', 'start_date', 'end_date', 'is_current', 'type', 'url', 'description', 'image_mime_type', 'created_at', 'updated_at'])->with('skills');

        if ($search) {
            $query->where('institution', 'like', "%{$search}%")
                  ->orWhere('degree', 'like', "%{$search}%");
        }

        $educations = $query->orderBy('start_date', 'desc')->get();

        return Inertia::render('Admin/Education/Index', [
            'educations' => $educations,
            'filters' => [
                'search' => $search
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Education/Create', [
            'availableSkills' => Skill::orderBy('category')->orderBy('name')->get(['id', 'name', 'category']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'institution' => 'required|string|max:255',
            'degree' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp,svg|max:2048',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'boolean',
            'type' => 'required|string|in:education,certificate',
            'url' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
        ]);

        $skillIds = $validated['skills'] ?? [];
        unset($validated['skills'], $validated['image']);

        $education = Education::create($validated);
        $education->skills()->sync($skillIds);

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $education->update([
                'image_data' => file_get_contents($file->getRealPath()),
                'image_mime_type' => $file->getClientMimeType() ?: 'image/png',
            ]);
        }

        return redirect()->route('admin.education.index')->with('success', 'Education/Certificate created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Education $education)
    {
        // Not used
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Education $education)
    {
        $education->load('skills');
        $education->makeHidden('image_data');

        return Inertia::render('Admin/Education/Edit', [
            'education' => $education,
            'availableSkills' => Skill::orderBy('category')->orderBy('name')->get(['id', 'name', 'category']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Education $education)
    {
        $validated = $request->validate([
            'institution' => 'required|string|max:255',
            'degree' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp,svg|max:2048',
            'remove_image' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'boolean',
            'type' => 'required|string|in:education,certificate',
            'url' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
        ]);

        $skillIds = $validated['skills'] ?? [];
        unset($validated['skills'], $validated['image'], $validated['remove_image']);

        $education->update($validated);
        $education->skills()->sync($skillIds);

        // Handle image removal
        if ($request->boolean('remove_image')) {
            $education->update([
                'image_data' => null,
                'image_mime_type' => null,
            ]);
        }

        // Handle image upload (overrides removal if both sent)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $education->update([
                'image_data' => file_get_contents($file->getRealPath()),
                'image_mime_type' => $file->getClientMimeType() ?: 'image/png',
            ]);
        }

        return redirect()->route('admin.education.index')->with('success', 'Education/Certificate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Education $education)
    {
        $education->delete();
        return redirect()->back()->with('success', 'Education deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        Education::whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', 'Selected records deleted successfully.');
    }

    /**
     * Serve the education image from database (public route).
     */
    public function serveImage($id)
    {
        $education = Education::withoutGlobalScope('withoutBlob')->findOrFail($id);

        if (empty($education->image_data)) {
            abort(404);
        }

        return response($education->image_data, 200, [
            'Content-Type' => $education->image_mime_type ?: 'image/png',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
