<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\DistributorShippingRate;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingRateController extends Controller
{
    /**
     * List the distributor's shipping rates, grouped by country.
     */
    public function index()
    {
        $user = Auth::user();

        $rates = $user->shippingRates()
            ->with(['city', 'district'])
            ->orderBy('country_code')
            ->orderBy('city_id')
            ->orderBy('district_id')
            ->get();

        // Countries the distributor can ship to — its own country first, plus any
        // country that already has a rate. (Most distributors ship within one country.)
        $countries = Country::active()->orderBy('sort_order')->get();

        return view('distributor.shipping.index', compact('rates', 'countries'));
    }

    /** Cities of a country (AJAX). */
    public function cities(string $countryCode)
    {
        $cities = City::forCountry($countryCode)->active()
            ->orderBy('sort_order')->orderBy('name')
            ->get(['id', 'name', 'name_ar']);

        return response()->json(['success' => true, 'cities' => $cities]);
    }

    /** Districts of a city (AJAX). */
    public function districts(City $city)
    {
        $districts = $city->districts()->active()
            ->orderBy('sort_order')->orderBy('name')
            ->get(['id', 'name', 'name_ar']);

        return response()->json(['success' => true, 'districts' => $districts]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'country_code' => 'required|string|max:3|exists:countries,code',
            'city_id' => 'nullable|exists:cities,id',
            'district_id' => 'nullable|exists:districts,id',
            'shipping_cost' => 'required|numeric|min:0',
            'delivery_days_min' => 'nullable|integer|min:0',
            'delivery_days_max' => 'nullable|integer|min:0',
        ]);

        // District requires a city
        if (!empty($data['district_id']) && empty($data['city_id'])) {
            return back()->withErrors(['city_id' => app()->getLocale() == 'ar'
                ? 'اختر المدينة قبل الحي' : 'Select a city before a district'])->withInput();
        }

        // Prevent duplicate scope for the same distributor
        $exists = $user->shippingRates()
            ->where('country_code', $data['country_code'])
            ->where('city_id', $data['city_id'] ?? null)
            ->where('district_id', $data['district_id'] ?? null)
            ->exists();

        if ($exists) {
            return back()->with('error', app()->getLocale() == 'ar'
                ? 'يوجد سعر شحن لنفس النطاق بالفعل' : 'A shipping rate for this scope already exists')->withInput();
        }

        $data['distributor_id'] = $user->id;
        $data['currency'] = 'AED';
        $data['is_active'] = true;

        DistributorShippingRate::create($data);

        return redirect()->route('distributor.shipping.index')
            ->with('success', app()->getLocale() == 'ar' ? 'تمت إضافة سعر الشحن' : 'Shipping rate added');
    }

    public function update(Request $request, DistributorShippingRate $rate)
    {
        $this->authorizeRate($rate);

        $data = $request->validate([
            'shipping_cost' => 'required|numeric|min:0',
            'delivery_days_min' => 'nullable|integer|min:0',
            'delivery_days_max' => 'nullable|integer|min:0',
        ]);

        $rate->update($data);

        return redirect()->route('distributor.shipping.index')
            ->with('success', app()->getLocale() == 'ar' ? 'تم تحديث سعر الشحن' : 'Shipping rate updated');
    }

    public function toggle(DistributorShippingRate $rate)
    {
        $this->authorizeRate($rate);
        $rate->update(['is_active' => !$rate->is_active]);

        return response()->json(['success' => true, 'is_active' => $rate->is_active]);
    }

    public function destroy(DistributorShippingRate $rate)
    {
        $this->authorizeRate($rate);
        $rate->delete();

        return redirect()->route('distributor.shipping.index')
            ->with('success', app()->getLocale() == 'ar' ? 'تم حذف سعر الشحن' : 'Shipping rate deleted');
    }

    private function authorizeRate(DistributorShippingRate $rate): void
    {
        abort_unless($rate->distributor_id === Auth::id(), 403);
    }
}
