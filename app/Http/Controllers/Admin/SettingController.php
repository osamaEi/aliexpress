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

        // Define settings that should be created if they don't exist
        $newSettings = [
            // Bilingual site info
            'site_name' => ['type' => 'text', 'description' => 'Site name in English'],
            'site_name_ar' => ['type' => 'text', 'description' => 'Site name in Arabic'],
            'site_description' => ['type' => 'textarea', 'description' => 'Site description in English'],
            'site_description_ar' => ['type' => 'textarea', 'description' => 'Site description in Arabic'],
            // Dashboard banners
            'seller_dashboard_banner' => ['type' => 'image', 'description' => 'Banner for seller dashboard'],
            'distributor_dashboard_banner' => ['type' => 'image', 'description' => 'Banner for distributor dashboard'],
            'buyer_dashboard_banner' => ['type' => 'image', 'description' => 'Banner for buyer dashboard'],
            // Wallet settings
            'min_withdrawal_amount' => ['type' => 'number', 'description' => 'Minimum withdrawal amount'],
            'max_withdrawal_amount' => ['type' => 'number', 'description' => 'Maximum withdrawal amount'],
            'min_deposit_amount' => ['type' => 'number', 'description' => 'Minimum deposit amount'],
            'withdrawal_fee_percentage' => ['type' => 'number', 'description' => 'Withdrawal fee percentage'],
            // Subscription locks
            'require_subscription_seller' => ['type' => 'boolean', 'description' => 'Require subscription for sellers'],
            'require_subscription_distributor' => ['type' => 'boolean', 'description' => 'Require subscription for distributors'],
            'lock_products_on_subscription_expiry' => ['type' => 'boolean', 'description' => 'Lock products when subscription expires'],
            'subscription_grace_period_days' => ['type' => 'number', 'description' => 'Grace period in days after subscription expiry'],
            // reCAPTCHA
            'recaptcha_site_key' => ['type' => 'text', 'description' => 'reCAPTCHA v3 Site Key'],
            'recaptcha_secret_key' => ['type' => 'text', 'description' => 'reCAPTCHA v3 Secret Key'],
            'recaptcha_enabled' => ['type' => 'boolean', 'description' => 'Enable reCAPTCHA verification'],
            // Commission profit type
            'admin_profit_commission' => ['type' => 'number', 'description' => 'Platform commission rate percentage per order'],
        ];

        foreach ($request->settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            // If setting doesn't exist, check if it's a known new setting and create it
            if (!$setting && isset($newSettings[$key])) {
                $setting = Setting::create([
                    'key' => $key,
                    'value' => null,
                    'type' => $newSettings[$key]['type'],
                    'description' => $newSettings[$key]['description'],
                ]);
            }

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

            // Handle boolean settings (checkboxes)
            if ($setting->type === 'boolean') {
                $value = $request->has("settings.{$key}") ? '1' : '0';
            }

            Setting::set($key, $value, $setting->type, $setting->description);
        }

        // Handle unchecked boolean settings (they won't be in the request)
        foreach ($newSettings as $key => $config) {
            if ($config['type'] === 'boolean' && !$request->has("settings.{$key}")) {
                $setting = Setting::where('key', $key)->first();
                if ($setting) {
                    Setting::set($key, '0', 'boolean', $config['description']);
                }
            }
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
