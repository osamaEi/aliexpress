<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $settings = Setting::orderBy('key')->get()->groupBy('type');
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            if (!$setting) {
                continue;
            }

            // Handle image uploads
            if ($setting->type === 'image' && $request->hasFile("settings.{$key}")) {
                // Delete old image if exists
                if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                    Storage::disk('public')->delete($setting->value);
                }

                // Store new image
                $path = $request->file("settings.{$key}")->store('settings', 'public');
                $value = $path;
            }

            Setting::set($key, $value, $setting->type, $setting->description);
        }

        // Clear settings cache
        Setting::clearCache();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully');
    }

    /**
     * Delete an image setting
     */
    public function deleteImage(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
        ]);

        $setting = Setting::where('key', $request->key)->first();

        if ($setting && $setting->type === 'image' && $setting->value) {
            // Delete the image file
            if (Storage::disk('public')->exists($setting->value)) {
                Storage::disk('public')->delete($setting->value);
            }

            // Clear the setting value
            Setting::set($request->key, null, 'image', $setting->description);
            Setting::clearCache();

            return response()->json(['success' => true, 'message' => 'Image deleted successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Image not found'], 404);
    }
}
