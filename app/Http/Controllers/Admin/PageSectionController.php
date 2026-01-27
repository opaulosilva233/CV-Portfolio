<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageSection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageSectionController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Sections/Index', [
            'sections' => PageSection::orderBy('sort_order')->get()
        ]);
    }

    public function update(Request $request, PageSection $section)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $section->update($validated);

        return redirect()->back();
    }
}
