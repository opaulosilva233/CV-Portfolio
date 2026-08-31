<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Project::query()->with(['skills', 'images']);

        if ($search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $projects = $query->orderBy('completed_at', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->get();

        return Inertia::render('Admin/Projects/Index', [
            'projects' => $projects,
            'filters' => [
                'search' => $search
            ]
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
        $project->load(['skills', 'images.translations']);

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
            'project_url' => 'nullable|url',
            'github_url' => 'nullable|url',
            'is_featured' => 'boolean',
            'in_progress' => 'boolean',
            'completed_at' => 'nullable|date',
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,webp,svg|max:2048',
            'image_metadata' => 'nullable|json',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $skillIds = $validated['skills'] ?? [];
        $imageMetadata = json_decode($request->input('image_metadata', '[]'), true);
        
        unset($validated['skills'], $validated['images'], $validated['image_metadata']);

        $project = Project::create($validated);
        $project->skills()->sync($skillIds);

        if ($request->hasFile('images')) {
            $this->updateGallery($project, $request->file('images'), $imageMetadata);
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project created successfully.');
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'project_url' => 'nullable|url',
            'github_url' => 'nullable|url',
            'is_featured' => 'boolean',
            'in_progress' => 'boolean',
            'completed_at' => 'nullable|date',
            'skills' => 'nullable|array',
            'skills.*' => 'integer|exists:skills,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,webp,svg|max:2048',
            'image_metadata' => 'nullable|json', // Full state of the gallery metadata
            'remove_images' => 'nullable|array', // filenames to remove
        ]);

        if ($project->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $skillIds = $validated['skills'] ?? [];
        $imageMetadata = json_decode($request->input('image_metadata', '[]'), true);
        $removeImages = $request->input('remove_images', []);

        unset($validated['skills'], $validated['images'], $validated['image_metadata'], $validated['remove_images']);

        $project->update($validated);
        $project->skills()->sync($skillIds);

        $this->updateGallery($project, $request->file('images', []), $imageMetadata, $removeImages);

        return redirect()->route('admin.projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->back();
    }

    public function reorder(Request $request)
    {
        $orderedIds = $request->input('ids');

        foreach ($orderedIds as $index => $id) {
            Project::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        Project::whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', 'Selected projects deleted successfully.');
    }

    /**
     * Serve a specific project image from database.
     */
    public function serveImage(Project $project, ?string $filename = null)
    {
        $query = ProjectImage::withoutGlobalScope('withoutBlob')->where('project_id', $project->id);

        if (!$filename || $filename === 'principal' || str_starts_with($filename, 'principal.')) {
            $image = (clone $query)->where('is_principal', true)->first() ?? $query->first();
        } else {
            $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
            $image = $query->where(function ($q) use ($filename, $nameWithoutExt) {
                if (is_numeric($filename)) {
                    $q->where('id', (int)$filename);
                } elseif (is_numeric($nameWithoutExt)) {
                    $q->where('id', (int)$nameWithoutExt);
                } else {
                    $q->where('filename', $filename)
                      ->orWhere('filename', 'like', $nameWithoutExt . '.%');
                }
            })->first();
        }

        if (!$image || empty($image->image_data)) {
            abort(404);
        }

        return response($image->image_data, 200, [
            'Content-Type' => $image->mime_type ?: 'image/png',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    /**
     * Update the project gallery in the database.
     */
    private function updateGallery(Project $project, array $newFiles, array $metadata, array $removeFilenames = []): void
    {
        // 1. Handle removals
        if (!empty($removeFilenames)) {
            foreach ($removeFilenames as $identifier) {
                $nameWithoutExt = pathinfo($identifier, PATHINFO_FILENAME);
                $project->images()
                    ->where(function ($q) use ($identifier, $nameWithoutExt) {
                        $q->where('filename', $identifier)
                          ->orWhere('filename', 'like', $nameWithoutExt . '.%');
                        if (is_numeric($identifier)) {
                            $q->orWhere('id', (int)$identifier);
                        }
                        if (is_numeric($nameWithoutExt)) {
                            $q->orWhere('id', (int)$nameWithoutExt);
                        }
                    })
                    ->delete();
            }
        }

        // 2. Handle switching principal if an existing item was selected
        $principalExisting = $metadata['is_principal_existing'] ?? null;
        if ($principalExisting) {
            $nameWithoutExt = pathinfo($principalExisting, PATHINFO_FILENAME);
            $target = $project->images()
                ->where(function ($q) use ($principalExisting, $nameWithoutExt) {
                    $q->where('filename', $principalExisting)
                      ->orWhere('filename', 'like', $nameWithoutExt . '.%');
                    if (is_numeric($principalExisting)) {
                        $q->orWhere('id', (int)$principalExisting);
                    }
                    if (is_numeric($nameWithoutExt)) {
                        $q->orWhere('id', (int)$nameWithoutExt);
                    }
                })
                ->first();

            if ($target) {
                $project->images()->where('id', '!=', $target->id)->update(['is_principal' => false]);
                $target->update(['is_principal' => true]);
            }
        }

        // 3. Update descriptions on existing images
        if (!empty($metadata['descriptions'])) {
            foreach ($metadata['descriptions'] as $key => $desc) {
                if (str_starts_with($key, 'new_')) continue;

                $nameWithoutExt = pathinfo($key, PATHINFO_FILENAME);
                $target = $project->images()
                    ->where(function ($q) use ($key, $nameWithoutExt) {
                        $q->where('filename', $key)
                          ->orWhere('filename', 'like', $nameWithoutExt . '.%');
                        if (is_numeric($key)) {
                            $q->orWhere('id', (int)$key);
                        }
                        if (is_numeric($nameWithoutExt)) {
                            $q->orWhere('id', (int)$nameWithoutExt);
                        }
                    })
                    ->first();

                if ($target) {
                    $target->update(['description' => $desc]);
                }
            }
        }

        // 4. Save new files
        $maxSortOrder = (int)($project->images()->max('sort_order') ?? 0);

        foreach ($newFiles as $index => $file) {
            $isPrincipal = (bool)($metadata['new_' . $index]['is_principal'] ?? false);

            if ($isPrincipal) {
                $project->images()->update(['is_principal' => false]);
            }

            $description = $metadata['descriptions']['new_' . $index] ?? null;

            $project->images()->create([
                'filename' => $file->getClientOriginalName(),
                'image_data' => file_get_contents($file->getRealPath()),
                'mime_type' => $file->getClientMimeType() ?: 'image/png',
                'description' => $description,
                'is_principal' => $isPrincipal,
                'sort_order' => ++$maxSortOrder,
            ]);
        }

        // 5. Ensure at least one principal image exists
        if ($project->images()->count() > 0 && !$project->images()->where('is_principal', true)->exists()) {
            $first = $project->images()->orderBy('sort_order', 'asc')->first();
            if ($first) {
                $first->update(['is_principal' => true]);
            }
        }
    }
}
