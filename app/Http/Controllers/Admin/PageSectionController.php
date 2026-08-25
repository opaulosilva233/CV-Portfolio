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
        PageSection::clearPortfolioCache();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'section' => $section]);
        }

        return redirect()->back();
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:page_sections,id',
        ]);

        foreach ($validated['ids'] as $index => $id) {
            PageSection::where('id', $id)->update(['sort_order' => $index]);
        }

        PageSection::clearPortfolioCache();

        return response()->json(['success' => true]);
    }
}
