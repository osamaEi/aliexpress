<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SellerSubcategoryProfit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerProfileCompletionController extends Controller
{
    /**
     * Show profit settings form
     */
    public function showProfitForm()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $isAr = app()->getLocale() == 'ar';

        // Check if payment method is set up first
        if (!$user->hasPaymentMethodSetup()) {
            return redirect()->route('profile.edit')
                ->with('warning', $isAr
                    ? 'يرجى إكمال بيانات الملف الشخصي وطريقة السحب أولاً'
                    : 'Please complete your profile and withdrawal method first');
        }

        // If already completed profit settings, redirect to dashboard
        if ($user->profit_settings_completed) {
            return redirect()->route('dashboard');
        }

        // Get subcategories that seller selected during registration
        $subActivityIds = json_decode($user->sub_activity, true) ?? [];

        // Get categories with their parent info
        $subcategories = Category::whereIn('id', $subActivityIds)
            ->with('parent')
            ->get()
            ->groupBy('parent_id');

        return view('seller.profit-setup', [
            'user' => $user,
            'subcategories' => $subcategories,
        ]);
    }

    /**
     * Save profit settings
     */
    public function storeProfitSettings(Request $request)
    {
        $isAr = app()->getLocale() == 'ar';
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Get all subcategory IDs
        $subActivityIds = json_decode($user->sub_activity, true) ?? [];

        // Validate that all subcategories have profit values
        $rules = [];
        $messages = [];

        foreach ($subActivityIds as $subcategoryId) {
            $rules["profit_{$subcategoryId}"] = 'required|numeric|min:0|max:100';
            $messages["profit_{$subcategoryId}.required"] = $isAr
                ? 'نسبة الربح مطلوبة لجميع الفئات'
                : 'Profit percentage is required for all categories';
            $messages["profit_{$subcategoryId}.numeric"] = $isAr
                ? 'نسبة الربح يجب أن تكون رقم'
                : 'Profit percentage must be a number';
            $messages["profit_{$subcategoryId}.min"] = $isAr
                ? 'نسبة الربح يجب أن تكون 0 على الأقل'
                : 'Profit percentage must be at least 0';
            $messages["profit_{$subcategoryId}.max"] = $isAr
                ? 'نسبة الربح يجب ألا تزيد عن 100'
                : 'Profit percentage must not exceed 100';
        }

        $validated = $request->validate($rules, $messages);

        // Delete existing profit settings for this seller
        SellerSubcategoryProfit::where('user_id', $user->id)->delete();

        // Create new profit settings
        foreach ($subActivityIds as $subcategoryId) {
            $profitPercentage = $validated["profit_{$subcategoryId}"];

            SellerSubcategoryProfit::create([
                'user_id' => $user->id,
                'category_id' => $subcategoryId,
                'profit_percentage' => $profitPercentage,
                'is_active' => true,
            ]);
        }

        // Mark profit settings as completed
        $user->update([
            'profit_settings_completed' => true,
        ]);

        return redirect()->route('dashboard')->with('success', $isAr
            ? 'تم حفظ إعدادات الربح بنجاح. يمكنك الآن البدء في استخدام النظام!'
            : 'Profit settings saved successfully. You can now start using the system!');
    }
}
