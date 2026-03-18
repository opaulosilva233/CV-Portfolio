<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InterestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $interests = Interest::orderBy('order')->get();
        return inertia('Admin/Interests/Index', [
            'interests' => $interests
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Admin/Interests/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'icon' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $order = Interest::max('order') + 1;
        $validated['order'] = $order;
        $validated['is_active'] = $validated['is_active'] ?? true;

        Interest::create($validated);

        return redirect()->route('admin.interests.index')->with('success', 'Interest created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Interest $interest)
    {
        return inertia('Admin/Interests/Edit', [
            'interest' => $interest
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Interest $interest)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'icon' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        
        $interest->update($validated);

        return redirect()->route('admin.interests.index')->with('success', 'Interest updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Interest $interest)
    {
        $interest->delete();
        return redirect()->route('admin.interests.index')->with('success', 'Interest deleted successfully.');
    }
    
    /**
     * Update the order of the interests.
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'interests' => 'required|array',
            'interests.*.id' => 'required|exists:interests,id',
            'interests.*.order' => 'required|integer',
        ]);

        foreach ($request->interests as $interestData) {
            Interest::where('id', $interestData['id'])->update(['order' => $interestData['order']]);
        }

        return response()->json(['message' => 'Order updated successfully']);
    }
}
