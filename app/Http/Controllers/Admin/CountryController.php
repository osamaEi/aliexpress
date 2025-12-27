<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * Display a listing of countries
     */
    public function index(Request $request)
    {
        $query = Country::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_source')) {
            $query->where('is_source', $request->is_source);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $countries = $query->orderBy('sort_order')->paginate(20);

        // Stats
        $stats = [
            'total' => Country::count(),
            'active' => Country::active()->count(),
            'source' => Country::source()->count(),
        ];

        return view('admin.countries.index', compact('countries', 'stats'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.countries.create');
    }

    /**
     * Store a new country
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:3|unique:countries,code',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'flag' => 'nullable|string|max:10',
            'phone_code' => 'nullable|string|max:10',
            'currency_code' => 'nullable|string|max:3',
            'is_active' => 'boolean',
            'is_source' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->has('is_active');
        $data['is_source'] = $request->has('is_source');
        $data['sort_order'] = $request->sort_order ?? 0;

        Country::create($data);

        return redirect()->route('admin.countries.index')
            ->with('success', app()->getLocale() == 'ar' ? 'تم إضافة الدولة بنجاح' : 'Country added successfully');
    }

    /**
     * Show edit form
     */
    public function edit(Country $country)
    {
        return view('admin.countries.edit', compact('country'));
    }

    /**
     * Update a country
     */
    public function update(Request $request, Country $country)
    {
        $request->validate([
            'code' => 'required|string|max:3|unique:countries,code,' . $country->id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'flag' => 'nullable|string|max:10',
            'phone_code' => 'nullable|string|max:10',
            'currency_code' => 'nullable|string|max:3',
            'is_active' => 'boolean',
            'is_source' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->has('is_active');
        $data['is_source'] = $request->has('is_source');
        $data['sort_order'] = $request->sort_order ?? 0;

        $country->update($data);

        return redirect()->route('admin.countries.index')
            ->with('success', app()->getLocale() == 'ar' ? 'تم تحديث الدولة بنجاح' : 'Country updated successfully');
    }

    /**
     * Toggle country active status
     */
    public function toggleStatus(Country $country)
    {
        $country->update(['is_active' => !$country->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $country->is_active,
            'message' => $country->is_active
                ? (app()->getLocale() == 'ar' ? 'تم تفعيل الدولة' : 'Country activated')
                : (app()->getLocale() == 'ar' ? 'تم تعطيل الدولة' : 'Country deactivated'),
        ]);
    }

    /**
     * Delete a country
     */
    public function destroy(Country $country)
    {
        // Check if country is used by any users
        $usersCount = User::where('country', $country->code)->count();

        if ($usersCount > 0) {
            return redirect()->route('admin.countries.index')
                ->with('error', app()->getLocale() == 'ar'
                    ? "لا يمكن حذف الدولة - يوجد {$usersCount} مستخدم مرتبط بها"
                    : "Cannot delete country - {$usersCount} users are associated with it");
        }

        $country->delete();

        return redirect()->route('admin.countries.index')
            ->with('success', app()->getLocale() == 'ar' ? 'تم حذف الدولة بنجاح' : 'Country deleted successfully');
    }
}
