<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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
            'project_url' => 'nullable|url',
            'github_url' => 'nullable|url',
            'tech_stack' => 'nullable|array',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
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
            'tech_stack' => 'nullable|array',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
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
        $dir = storage_path('projects/' . $project->id);
        if (is_dir($dir)) {
            File::deleteDirectory($dir);
        }

        $project->delete();
        return redirect()->back();
    }

    /**
     * Serve a specific project image from storage.
     */
    public function serveImage(Project $project, string $filename = null)
    {
        if (!$filename) {
            // Fallback for old routes or if filename omitted
            $files = glob(storage_path('projects/' . $project->id . '/principal.*'));
            if (empty($files)) abort(404);
            $path = $files[0];
        } else {
            $path = storage_path('projects/' . $project->id . '/' . $filename);
        }

        if (!File::exists($path) || is_dir($path) || $filename === 'metadata.json') {
            abort(404);
        }

        return response()->file($path);
    }

    /**
     * Update the project gallery.
     */
    private function updateGallery(Project $project, array $newFiles, array $metadata, array $removeFilenames = []): void
    {
        $dir = storage_path('projects/' . $project->id);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        // 1. Handle removals
        foreach ($removeFilenames as $filename) {
            $path = $dir . '/' . $filename;
            if (File::exists($path)) File::delete($path);
        }

        // 2. Handle switching principal if it's an existing file
        $principalExisting = $metadata['is_principal_existing'] ?? null;
        if ($principalExisting && $principalExisting !== 'principal') {
            $oldPrincipals = glob($dir . '/principal.*');
            $targetFiles = glob($dir . '/' . $principalExisting . '.*');

            if (!empty($targetFiles)) {
                $targetFile = $targetFiles[0];
                $targetExt = pathinfo($targetFile, PATHINFO_EXTENSION);
                
                if (!empty($oldPrincipals)) {
                    $oldPrincipal = $oldPrincipals[0];
                    $oldExt = pathinfo($oldPrincipal, PATHINFO_EXTENSION);
                    
                    // Find next number for old principal
                    $existingNums = array_map(fn($f) => pathinfo($f, PATHINFO_FILENAME), glob($dir . '/*.*'));
                    $nextNum = 1;
                    while (in_array((string)$nextNum, $existingNums)) $nextNum++;
                    
                    rename($oldPrincipal, $dir . '/' . $nextNum . '.' . $oldExt);
                }
                
                rename($targetFile, $dir . '/principal.' . $targetExt);
            }
        }

        // 3. Save new files
        $existingFiles = glob($dir . '/*.*');
        $existingNums = array_filter(array_map(fn($f) => pathinfo($f, PATHINFO_FILENAME), $existingFiles), 'is_numeric');
        $nextNumber = empty($existingNums) ? 1 : max(array_map('intval', $existingNums)) + 1;

        foreach ($newFiles as $index => $file) {
            $isPrincipal = ($metadata['new_' . $index]['is_principal'] ?? false);
            
            if ($isPrincipal) {
                // Move old principal to a number first
                $oldPrincipals = glob($dir . '/principal.*');
                if (!empty($oldPrincipals)) {
                    $oldPrincipal = $oldPrincipals[0];
                    $oldExt = pathinfo($oldPrincipal, PATHINFO_EXTENSION);
                    rename($oldPrincipal, $dir . '/' . $nextNumber++ . '.' . $oldExt);
                }
                $name = 'principal';
            } else {
                $name = (string)$nextNumber++;
            }

            $extension = $file->getClientOriginalExtension();
            $file->move($dir, $name . '.' . $extension);
        }

        // 4. Update metadata.json (descriptions)
        $currentLocale = \Illuminate\Support\Facades\App::getLocale();
        $metadataPath = $dir . '/metadata.json';
        $fullMetadata = file_exists($metadataPath) ? json_decode(file_get_contents($metadataPath), true) : [];
        
        if (isset($metadata['descriptions'])) {
            if (!isset($fullMetadata[$currentLocale])) $fullMetadata[$currentLocale] = [];
            
            // Clean up old descriptions for current locale if they don't exist anymore in disco
            $remainingFiles = array_map(fn($f) => pathinfo($f, PATHINFO_FILENAME), glob($dir . '/*.*'));
            $newLocaleMetadata = [];
            
            foreach ($metadata['descriptions'] as $key => $desc) {
                // If it's a new file, we don't know its final name yet easily here, 
                // but we can map 'new_0' to 'principal' or $nextNumber.
                // Actually, let's keep it simple: the frontend should ideally refresh 
                // or we accept that names change.
                // For now, let's just save what we got.
                if (str_starts_with($key, 'new_')) {
                    $idx = (int)str_replace('new_', '', $key);
                    $finalName = ($metadata['new_' . $idx]['is_principal'] ?? false) ? 'principal' : (string)($nextNumber - count($newFiles) + $idx);
                    $newLocaleMetadata[$finalName] = $desc;
                } else {
                    $newLocaleMetadata[$key] = $desc;
                }
            }
            $fullMetadata[$currentLocale] = $newLocaleMetadata;
        }

        File::put($metadataPath, json_encode($fullMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
