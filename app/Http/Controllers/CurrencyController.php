<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;

class CurrencyController extends Controller
{
    /**
     * Switch currency
     */
    public function switch($code)
    {
        $currency = Currency::where('code', $code)->where('is_active', true)->first();

        if ($currency) {
            session(['currency_code' => $currency->code]);
        }

        // Get the previous URL and update currency parameter if exists
        $previousUrl = url()->previous();
        $parsedUrl = parse_url($previousUrl);

        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);

            // Update currency in query params if it exists
            if (isset($queryParams['currency'])) {
                $queryParams['currency'] = $currency ? $currency->code : $code;
                $newQuery = http_build_query($queryParams);
                $newUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] .
                         (isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '') .
                         ($parsedUrl['path'] ?? '') . '?' . $newQuery;
                return redirect($newUrl);
            }
        }

        return redirect()->back();
    }
}
