<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\Skill;
use App\Models\Education;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class ExperienceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Experience::query()->with(['roles' => function ($q) {
            $q->orderBy('start_date', 'desc');
        }, 'skills']);

        if ($search) {
            $query->where('company', 'like', "%{$search}%")
                  ->orWhereHas('roles', function ($q) use ($search) {
                      $q->where('role', 'like', "%{$search}%");
                  });
        }

        $experiences = $query->get()->sortByDesc(function ($exp) {
            return $exp->roles->max('start_date');
        })->values();

        return Inertia::render('Admin/Experiences/Index', [
            'experiences' => $experiences,
            'filters' => [
                'search' => $search
            ]
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Experiences/Create', [
            'availableSkills' => Skill::orderBy('category')->orderBy('name')->get(['id', 'name', 'category']),
            'availableEducations' => Education::orderBy('start_date', 'desc')->get(['id', 'institution', 'degree']),
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
            'availableEducations' => Education::orderBy('start_date', 'desc')->get(['id', 'institution', 'degree']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company' => 'required|string|max:255',
            'location' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp,svg|max:2048',
            'roles' => 'required|array|min:1',
            'roles.*.role' => 'required|string|max:255',
            'roles.*.employment_type' => 'nullable|string|in:internship,temporary,full_time,part_time',
            'roles.*.start_date' => 'required|date',
            'roles.*.end_date' => 'nullable|date|after_or_equal:roles.*.start_date',
            'roles.*.is_current' => 'boolean',
            'roles.*.description' => 'nullable|string',
            'roles.*.education_id' => 'nullable|exists:education,id',
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

        // Handle image upload
        if ($request->hasFile('image')) {
            $this->saveImage($experience, $request->file('image'));
        }

        return redirect()->route('admin.experiences.index')->with('success', 'Experience created successfully.');
    }

    public function update(Request $request, Experience $experience)
    {
        $validated = $request->validate([
            'company' => 'required|string|max:255',
            'location' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp,svg|max:2048',
            'remove_image' => 'nullable|boolean',
            'roles' => 'required|array|min:1',
            'roles.*.role' => 'required|string|max:255',
            'roles.*.employment_type' => 'nullable|string|in:internship,temporary,full_time,part_time',
            'roles.*.start_date' => 'required|date',
            'roles.*.end_date' => 'nullable|date|after_or_equal:roles.*.start_date',
            'roles.*.is_current' => 'boolean',
            'roles.*.description' => 'nullable|string',
            'roles.*.education_id' => 'nullable|exists:education,id',
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

        // Handle image removal
        if ($request->boolean('remove_image')) {
            $this->deleteImage($experience);
        }

        // Handle image upload (overrides removal if both sent)
        if ($request->hasFile('image')) {
            $this->deleteImage($experience);
            $this->saveImage($experience, $request->file('image'));
        }

        return redirect()->route('admin.experiences.index')->with('success', 'Experience updated successfully.');
    }

    public function destroy(Experience $experience)
    {
        // Delete image folder
        $this->deleteImage($experience);

        $experience->delete();
        return redirect()->back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        $experiences = Experience::whereIn('id', $ids)->get();

        foreach ($experiences as $experience) {
            $this->deleteImage($experience);
            $experience->delete();
        }

        return redirect()->back()->with('success', 'Selected experiences deleted successfully.');
    }

    /**
     * Serve the experience image from storage (public route).
     */
    public function serveImage(Experience $experience)
    {
        $path = $experience->getImagePath();

        if (!$path) {
            abort(404);
        }

        return response()->file($path);
    }

    /**
     * Save an uploaded image to storage/experiences/{id}/logo.{ext}
     */
    private function saveImage(Experience $experience, $file): void
    {
        $dir = storage_path('experiences/' . $experience->id);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $extension = $file->getClientOriginalExtension();
        $file->move($dir, 'logo.' . $extension);
    }

    /**
     * Delete the image directory for an experience.
     */
    private function deleteImage(Experience $experience): void
    {
        $dir = storage_path('experiences/' . $experience->id);

        if (is_dir($dir)) {
            File::deleteDirectory($dir);
        }
    }
}
