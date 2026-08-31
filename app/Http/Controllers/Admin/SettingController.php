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

        // Handle hero image upload/removal
        if ($request->boolean('remove_hero_image')) {
            SiteSetting::updateOrCreate(
                ['key' => 'hero_image'],
                [
                    'value' => null,
                    'image_data' => null,
                    'image_mime_type' => null,
                ]
            );
            unset($data['hero_image']); // Don't override it with the text field later
        }

        if ($request->hasFile('hero_image_file')) {
            $file = $request->file('hero_image_file');
            SiteSetting::updateOrCreate(
                ['key' => 'hero_image'],
                [
                    'value' => route('settings.hero-image') . '?v=' . time(),
                    'image_data' => file_get_contents($file->getRealPath()),
                    'image_mime_type' => $file->getClientMimeType() ?: 'image/jpeg',
                ]
            );
            unset($data['hero_image']);
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
        $setting = SiteSetting::where('key', 'hero_image')->first();

        if (!$setting || empty($setting->image_data)) {
            abort(404);
        }

        return response($setting->image_data, 200, [
            'Content-Type' => $setting->image_mime_type ?: 'image/jpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
