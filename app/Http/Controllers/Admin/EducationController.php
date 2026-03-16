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
    public function index()
    {
        $educations = Education::with('skills')->orderBy('start_date', 'desc')->get();

        return Inertia::render('Admin/Education/Index', [
            'educations' => $educations
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
            $this->saveImage($education, $request->file('image'));
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
            $this->deleteImage($education);
        }

        // Handle image upload (overrides removal if both sent)
        if ($request->hasFile('image')) {
            $this->deleteImage($education);
            $this->saveImage($education, $request->file('image'));
        }

        return redirect()->route('admin.education.index')->with('success', 'Education/Certificate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Education $education)
    {
        // Delete image folder
        $this->deleteImage($education);

        $education->delete();
        return redirect()->back()->with('success', 'Education deleted successfully.');
    }

    /**
     * Serve the education image from storage (public route).
     */
    public function serveImage(Education $education)
    {
        $path = $education->getImagePath();

        if (!$path) {
            abort(404);
        }

        return response()->file($path);
    }

    /**
     * Save an uploaded image to storage/educations/{id}/logo.{ext}
     */
    private function saveImage(Education $education, $file): void
    {
        $dir = storage_path('educations/' . $education->id);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $extension = $file->getClientOriginalExtension();
        $file->move($dir, 'logo.' . $extension);
    }

    /**
     * Delete the image directory for an education.
     */
    private function deleteImage(Education $education): void
    {
        $dir = storage_path('educations/' . $education->id);

        if (is_dir($dir)) {
            File::deleteDirectory($dir);
        }
    }
}
