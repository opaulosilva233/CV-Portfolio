<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'locale' => 'required|string|in:en,pt,nl',
        ]);

        session()->put('locale', $validated['locale']);

        return redirect()->back();
    }
}
