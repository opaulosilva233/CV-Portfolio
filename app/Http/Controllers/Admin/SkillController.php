<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SkillController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Skills/Index', [
            'skills' => Skill::orderBy('sort_order')->orderBy('proficiency', 'desc')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'proficiency' => 'required|integer|min:0|max:100',
            'icon' => 'nullable|string',
            'sort_order' => 'integer',
        ]);

        Skill::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'proficiency' => 'required|integer|min:0|max:100',
            'icon' => 'nullable|string',
            'sort_order' => 'integer',
        ]);

        $skill->update($validated);

        return redirect()->back();
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();
        return redirect()->back();
    }
}
