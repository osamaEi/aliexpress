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
            // Set default currency from user profile if logged in and is seller
            if (auth()->check() && auth()->user()->user_type === 'seller' && auth()->user()->default_currency) {
                $currency = Currency::where('code', auth()->user()->default_currency)->first();
                if ($currency) {
                    session([
                        'currency_code' => $currency->code,
                        'currency_symbol' => $currency->symbol,
                        'currency_rate' => $currency->exchange_rate,
                    ]);
                }
            }

            // If still not set, use system default currency
            if (!session()->has('currency_code')) {
                $defaultCurrency = Currency::default();
                if ($defaultCurrency) {
                    session([
                        'currency_code' => $defaultCurrency->code,
                        'currency_symbol' => $defaultCurrency->symbol,
                        'currency_rate' => $defaultCurrency->exchange_rate,
                    ]);
                } else {
                    session([
                        'currency_code' => 'USD',
                        'currency_symbol' => '$',
                        'currency_rate' => 1.00,
                    ]);
                }
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

            // Update session with the fallback currency (all details)
            session([
                'currency_code' => $currency->code,
                'currency_symbol' => $currency->symbol,
                'currency_rate' => $currency->exchange_rate,
            ]);
        }

        view()->share('currentCurrency', $currency);

        return $next($request);
    }
}
