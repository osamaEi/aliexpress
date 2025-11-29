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

        return redirect()->back();
    }
}
