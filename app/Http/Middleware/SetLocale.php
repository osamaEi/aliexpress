<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from session, fallback to site setting, then default to 'ar'
        $locale = Session::get('locale', setting('site_language', 'ar'));

        // Validate locale
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = 'ar'; // Default to Arabic
        }

        // Set app locale
        App::setLocale($locale);

        return $next($request);
    }
}
