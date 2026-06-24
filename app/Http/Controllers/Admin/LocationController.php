<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\District;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Cities & districts management screen.
     * Admin picks a country, sees its cities, and drills into each city's districts.
     */
    public function index(Request $request)
    {
        $countries = Country::orderBy('sort_order')->orderBy('name')->get();

        $selectedCode = $request->get('country', optional($countries->first())->code);

        $cities = City::forCountry($selectedCode)
            ->withCount('districts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.locations.index', compact('countries', 'cities', 'selectedCode'));
    }

    /** List districts of a city (AJAX). */
    public function districts(City $city)
    {
        $districts = $city->districts()->orderBy('sort_order')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'city' => ['id' => $city->id, 'name' => $city->name, 'name_ar' => $city->name_ar],
            'districts' => $districts,
        ]);
    }

    /* ---------------- Cities ---------------- */

    public function storeCity(Request $request)
    {
        $data = $request->validate([
            'country_code' => 'required|string|max:3|exists:countries,code',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);
        $data['is_active'] = true;

        $city = City::create($data);

        return redirect()->route('admin.locations.index', ['country' => $city->country_code])
            ->with('success', app()->getLocale() == 'ar' ? 'تمت إضافة المدينة' : 'City added');
    }

    public function updateCity(Request $request, City $city)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $city->update($data);

        return redirect()->route('admin.locations.index', ['country' => $city->country_code])
            ->with('success', app()->getLocale() == 'ar' ? 'تم تحديث المدينة' : 'City updated');
    }

    public function toggleCity(City $city)
    {
        $city->update(['is_active' => !$city->is_active]);
        return response()->json(['success' => true, 'is_active' => $city->is_active]);
    }

    public function destroyCity(City $city)
    {
        $code = $city->country_code;
        $city->delete();

        return redirect()->route('admin.locations.index', ['country' => $code])
            ->with('success', app()->getLocale() == 'ar' ? 'تم حذف المدينة' : 'City deleted');
    }

    /* ---------------- Districts ---------------- */

    public function storeDistrict(Request $request)
    {
        $data = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);
        $data['is_active'] = true;

        $district = District::create($data);

        return response()->json([
            'success' => true,
            'district' => $district,
            'message' => app()->getLocale() == 'ar' ? 'تمت إضافة الحي' : 'District added',
        ]);
    }

    public function updateDistrict(Request $request, District $district)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $district->update($data);

        return response()->json(['success' => true, 'district' => $district]);
    }

    public function toggleDistrict(District $district)
    {
        $district->update(['is_active' => !$district->is_active]);
        return response()->json(['success' => true, 'is_active' => $district->is_active]);
    }

    public function destroyDistrict(District $district)
    {
        $district->delete();
        return response()->json(['success' => true]);
    }
}
