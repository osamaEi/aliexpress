<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminCategoryProfit;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminCategoryProfitController extends Controller
{
    /**
     * Display list of categories with their profit settings
     */
    public function index()
    {
        // Get all categories with their profit settings
        $categories = Category::with(['adminProfit', 'parent'])
            ->where('is_active', true)
            ->orderBy('parent_id')
            ->orderBy('order')
            ->get();

        return view('admin.category-profits.index', compact('categories'));
    }

    /**
     * Update or create profit for a category
     */
    public function update(Request $request, $categoryId)
    {
        $request->validate([
            'profit_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'is_active' => 'boolean',
        ]);

        $category = Category::findOrFail($categoryId);

        // Update or create profit for the main category
        $profit = AdminCategoryProfit::updateOrCreate(
            ['category_id' => $categoryId],
            [
                'profit_amount' => $request->profit_amount,
                'currency' => $request->currency,
                'is_active' => $request->has('is_active') ? true : false,
            ]
        );

        // If this is a parent category (has no parent_id), also save to all its subcategories
        $subcategoriesUpdated = 0;
        if (!$category->parent_id) {
            $subcategories = Category::where('parent_id', $categoryId)->get();

            foreach ($subcategories as $subcategory) {
                AdminCategoryProfit::updateOrCreate(
                    ['category_id' => $subcategory->id],
                    [
                        'profit_amount' => $request->profit_amount,
                        'currency' => $request->currency,
                        'is_active' => $request->has('is_active') ? true : false,
                    ]
                );
                $subcategoriesUpdated++;
            }
        }

        Log::info('Admin category profit updated', [
            'category_id' => $categoryId,
            'category_name' => $category->name,
            'profit_amount' => $request->profit_amount,
            'is_active' => $profit->is_active,
            'subcategories_updated' => $subcategoriesUpdated,
        ]);

        $message = $subcategoriesUpdated > 0
            ? "Profit setting saved for {$category->name} and {$subcategoriesUpdated} subcategories!"
            : 'Profit setting saved successfully!';

        return response()->json([
            'success' => true,
            'message' => $message,
            'profit' => $profit,
            'subcategories_updated' => $subcategoriesUpdated,
        ]);
    }

    /**
     * Delete profit setting for a category
     */
    public function destroy($categoryId)
    {
        $profit = AdminCategoryProfit::where('category_id', $categoryId)->first();

        if ($profit) {
            $category = Category::find($categoryId);

            Log::info('Admin category profit deleted', [
                'category_id' => $categoryId,
                'category_name' => $category ? $category->name : 'Unknown',
                'profit_amount' => $profit->profit_amount,
            ]);

            $profit->delete();

            return response()->json([
                'success' => true,
                'message' => 'Profit setting removed successfully!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Profit setting not found.',
        ], 404);
    }

    /**
     * Toggle active status
     */
    public function toggleActive($categoryId)
    {
        $profit = AdminCategoryProfit::where('category_id', $categoryId)->first();

        if ($profit) {
            $profit->is_active = !$profit->is_active;
            $profit->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully!',
                'is_active' => $profit->is_active,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Profit setting not found.',
        ], 404);
    }
}
