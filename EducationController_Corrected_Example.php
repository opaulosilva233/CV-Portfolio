<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Education;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EducationControllerCorrected extends Controller
{
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

        // Handle image upload - CORRECTED: using 'public' disk
        if ($request->hasFile('image')) {
            $this->saveImage($education, $request->file('image'));
        }

        return redirect()->route('admin.education.index')->with('success', 'Education/Certificate created successfully.');
    }

    /**
     * Save an uploaded image to storage/app/public/educations/{id}/logo.{ext}
     * CORRECTED: Now uses the 'public' disk so images persist and are accessible
     */
    private function saveImage(Education $education, $file): void
    {
        // ✅ CORRETO: Usa o disk 'public' que vai para storage/app/public/
        $path = $file->store("educations/{$education->id}", 'public');
        
        // Opcional: Salvar o caminho no banco de dados se necessário
        // $education->update(['image_path' => $path]);
    }

    /**
     * Delete the image directory for an education.
     */
    private function deleteImage(Education $education): void
    {
        // ✅ CORRETO: Deleta do disk 'public'
        Storage::disk('public')->deleteDirectory("educations/{$education->id}");
    }

    /**
     * Get the image URL for display in views
     */
    public function getImageUrl(Education $education): ?string
    {
        $path = "educations/{$education->id}/logo.jpg";
        
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }
        
        return null;
    }
}

// ============================================================================
// COMO USAR NO BLADE/FRONTEND:
// ============================================================================
//
// No seu componente Vue/Blade, use:
//
// ❌ ERRADO (atual):
// <img src="/storage/educations/{{ $education->id }}/logo.jpg">
//
// ✅ CORRETO:
// <img src="{{ Storage::disk('public')->url('educations/' . $education->id . '/logo.jpg') }}">
// ou
// <img src="{{ asset('storage/educations/' . $education->id . '/logo.jpg') }}">
//
// ============================================================================
// IMPORTANTE: Após alterar o código, execute no servidor:
// php artisan storage:link
// ============================================================================
