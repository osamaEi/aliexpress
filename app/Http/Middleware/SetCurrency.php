<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Currency;

class SetCurrency
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if currency is set in session
        if (!session()->has('currency_code')) {
            // Set default currency
            $defaultCurrency = Currency::default();
            if ($defaultCurrency) {
                session(['currency_code' => $defaultCurrency->code]);
            } else {
                session(['currency_code' => 'USD']);
            }
        }

        // Share currency with all views
        $currency = Currency::where('code', session('currency_code'))->first();
        if ($currency) {
            view()->share('currentCurrency', $currency);
        }

        return $next($request);
    }
}
