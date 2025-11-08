<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SellerSubcategoryProfit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SellerSubcategoryProfitController extends Controller
{
    /**
     * Display seller's subcategory profit settings
     */
    public function index()
    {
        $seller = Auth::user();

        // Get all subcategories (categories with parent_id)
        $subcategories = Category::whereNotNull('parent_id')
            ->with('parent')
            ->active()
            ->orderBy('parent_id')
            ->orderBy('order')
            ->get();

        // Get seller's existing profit settings
        $profitSettings = $seller->subcategoryProfits()
            ->with('category')
            ->get()
            ->keyBy('category_id');

        return view('seller.profits.index', compact('subcategories', 'profitSettings'));
    }

    /**
     * Store or update profit setting for a subcategory
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'profit_type' => 'required|in:percentage,fixed',
            'profit_value' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        try {
            $seller = Auth::user();

            // Verify it's a subcategory
            $category = Category::findOrFail($validated['category_id']);
            if (!$category->isSubcategory()) {
                return back()->with('error', __('messages.must_be_subcategory'));
            }

            // Create or update profit setting
            $profitSetting = SellerSubcategoryProfit::updateOrCreate(
                [
                    'user_id' => $seller->id,
                    'category_id' => $validated['category_id'],
                ],
                [
                    'profit_type' => $validated['profit_type'],
                    'profit_value' => $validated['profit_value'],
                    'currency' => config('paypal.currency', 'USD'),
                    'is_active' => $validated['is_active'] ?? true,
                ]
            );

            return back()->with('success', __('messages.profit_setting_saved'));

        } catch (\Exception $e) {
            Log::error('Profit Setting Error', [
                'seller_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', __('messages.profit_setting_failed'));
        }
    }

    /**
     * Update multiple profit settings at once
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'profits' => 'required|array',
            'profits.*.category_id' => 'required|exists:categories,id',
            'profits.*.profit_type' => 'required|in:percentage,fixed',
            'profits.*.profit_value' => 'required|numeric|min:0',
            'profits.*.is_active' => 'boolean',
        ]);

        try {
            $seller = Auth::user();

            DB::transaction(function () use ($seller, $validated) {
                foreach ($validated['profits'] as $profitData) {
                    // Verify it's a subcategory
                    $category = Category::findOrFail($profitData['category_id']);
                    if (!$category->isSubcategory()) {
                        continue;
                    }

                    SellerSubcategoryProfit::updateOrCreate(
                        [
                            'user_id' => $seller->id,
                            'category_id' => $profitData['category_id'],
                        ],
                        [
                            'profit_type' => $profitData['profit_type'],
                            'profit_value' => $profitData['profit_value'],
                            'currency' => config('paypal.currency', 'USD'),
                            'is_active' => $profitData['is_active'] ?? true,
                        ]
                    );
                }
            });

            return back()->with('success', __('messages.all_profit_settings_saved'));

        } catch (\Exception $e) {
            Log::error('Bulk Profit Update Error', [
                'seller_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', __('messages.profit_settings_failed'));
        }
    }

    /**
     * Toggle active status of a profit setting
     */
    public function toggleActive(SellerSubcategoryProfit $profit)
    {
        // Ensure the profit setting belongs to the authenticated seller
        if ($profit->user_id !== Auth::id()) {
            abort(403);
        }

        $profit->update(['is_active' => !$profit->is_active]);

        return back()->with('success', __('messages.profit_setting_updated'));
    }

    /**
     * Delete a profit setting
     */
    public function destroy(SellerSubcategoryProfit $profit)
    {
        // Ensure the profit setting belongs to the authenticated seller
        if ($profit->user_id !== Auth::id()) {
            abort(403);
        }

        $profit->delete();

        return back()->with('success', __('messages.profit_setting_deleted'));
    }

    /**
     * API endpoint to get profit setting for a specific subcategory
     */
    public function getProfitForSubcategory(Request $request, $categoryId)
    {
        $seller = Auth::user();

        $profitSetting = $seller->getProfitForSubcategory($categoryId);

        if (!$profitSetting) {
            return response()->json([
                'exists' => false,
                'profit_type' => 'percentage',
                'profit_value' => 0,
            ]);
        }

        return response()->json([
            'exists' => true,
            'profit_type' => $profitSetting->profit_type,
            'profit_value' => $profitSetting->profit_value,
            'is_active' => $profitSetting->is_active,
        ]);
    }
}
