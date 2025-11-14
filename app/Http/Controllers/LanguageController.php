<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Setting;

class LanguageController extends Controller
{
    /**
     * Switch application language
     */
    public function switch(Request $request, $locale)
    {
        // Validate locale
        if (!in_array($locale, ['en', 'ar'])) {
            abort(400, 'Invalid locale');
        }

        // Store locale in session
        Session::put('locale', $locale);

        // Set app locale
        App::setLocale($locale);

        // Update site_language setting if user is admin
        if (auth()->check() && auth()->user()->user_type === 'admin') {
            Setting::set('site_language', $locale, 'select', 'Default language for the website (ar = Arabic, en = English)');
            Setting::clearCache();
        }

        // Redirect back
        return redirect()->back();
    }
}
