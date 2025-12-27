<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyManagementController extends Controller
{
    /**
     * Display a listing of currencies
     */
    public function index()
    {
        $currencies = Currency::orderBy('sort_order')->get();
        return view('admin.currencies.index', compact('currencies'));
    }

    /**
     * Show the form for creating a new currency
     */
    public function create()
    {
        return view('admin.currencies.create');
    }

    /**
     * Store a newly created currency in storage
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:3|unique:currencies,code',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.0001',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->has('is_active');
        $data['is_default'] = $request->has('is_default');
        $data['sort_order'] = $request->sort_order ?? 0;

        // If this currency is set as default, remove default from others
        if ($data['is_default']) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        Currency::create($data);

        return redirect()->route('admin.currencies.index')
            ->with('success', __('messages.currency_created_successfully'));
    }

    /**
     * Show the form for editing the specified currency
     */
    public function edit(Currency $currency)
    {
        return view('admin.currencies.edit', compact('currency'));
    }

    /**
     * Update the specified currency in storage
     */
    public function update(Request $request, Currency $currency)
    {
        $request->validate([
            'code' => 'required|string|max:3|unique:currencies,code,' . $currency->id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.0001',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->has('is_active');
        $data['is_default'] = $request->has('is_default');
        $data['sort_order'] = $request->sort_order ?? 0;

        // If this currency is set as default, remove default from others
        if ($data['is_default']) {
            Currency::where('id', '!=', $currency->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $currency->update($data);

        return redirect()->route('admin.currencies.index')
            ->with('success', __('messages.currency_updated_successfully'));
    }

    /**
     * Remove the specified currency from storage
     */
    public function destroy(Currency $currency)
    {
        // Prevent deletion of default currency
        if ($currency->is_default) {
            return redirect()->route('admin.currencies.index')
                ->with('error', __('messages.cannot_delete_default_currency'));
        }

        $currency->delete();

        return redirect()->route('admin.currencies.index')
            ->with('success', __('messages.currency_deleted_successfully'));
    }

    /**
     * Toggle currency active status
     */
    public function toggleActive(Currency $currency)
    {
        $currency->update(['is_active' => !$currency->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $currency->is_active,
            'message' => __('messages.currency_status_updated')
        ]);
    }

    /**
     * Set currency as default
     */
    public function setDefault(Currency $currency)
    {
        // Remove default from all currencies
        Currency::where('is_default', true)->update(['is_default' => false]);

        // Set this currency as default
        $currency->update(['is_default' => true, 'is_active' => true]);

        return response()->json([
            'success' => true,
            'message' => __('messages.default_currency_set')
        ]);
    }
}
