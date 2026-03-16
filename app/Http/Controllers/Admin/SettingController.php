<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function edit()
    {
        return Inertia::render('Admin/Settings/Edit', [
            'settings' => SiteSetting::all()->keyBy('key'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method', 'hero_image_file', 'remove_hero_image']);

        // Handle hero image upload
        if ($request->boolean('remove_hero_image')) {
            $this->deleteHeroImage();
            SiteSetting::updateOrCreate(['key' => 'hero_image'], ['value' => null]);
            unset($data['hero_image']); // Don't override it with the text field later
        }

        if ($request->hasFile('hero_image_file')) {
            $this->deleteHeroImage();
            $file = $request->file('hero_image_file');
            
            $dir = storage_path('settings');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $extension = $file->getClientOriginalExtension();
            $file->move($dir, 'hero_image.' . $extension);
            
            // Set the URL to the new route
            $data['hero_image'] = route('settings.hero-image') . '?v=' . time(); 
        }

        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Settings updated.');
    }

    public function serveHeroImage()
    {
        $dir = storage_path('settings');
        $files = glob($dir . '/hero_image.*');

        if (empty($files)) {
            abort(404);
        }

        return response()->file($files[0]);
    }

    private function deleteHeroImage(): void
    {
        $dir = storage_path('settings');
        if (is_dir($dir)) {
            $files = glob($dir . '/hero_image.*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}
