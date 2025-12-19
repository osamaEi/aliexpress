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

        // If currency not found, fall back to default currency
        if (!$currency) {
            $currency = Currency::default();

            // If still no currency, create a default USD currency
            if (!$currency) {
                $currency = Currency::where('code', 'USD')->first();

                // Last resort: create USD currency if it doesn't exist
                if (!$currency) {
                    $currency = Currency::create([
                        'code' => 'USD',
                        'name' => 'US Dollar',
                        'symbol' => '$',
                        'exchange_rate' => 1.00,
                        'is_active' => true,
                        'is_default' => true,
                        'sort_order' => 1
                    ]);
                }
            }

            // Update session with the fallback currency
            session(['currency_code' => $currency->code]);
        }

        view()->share('currentCurrency', $currency);

        return $next($request);
    }
}
