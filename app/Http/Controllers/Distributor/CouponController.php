<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    /**
     * Display a listing of coupons for the authenticated distributor.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Coupon::where('store_id', $user->id);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)->where('end_date', '>=', now());
            } elseif ($request->status === 'expired') {
                $query->where('end_date', '<', now());
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search by code or title
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('title_ar', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%");
            });
        }

        $coupons = $query->latest()->paginate(15);

        return view('distributor.coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new coupon.
     */
    public function create()
    {
        $categories = \App\Models\Category::whereNull('parent_id')->where('is_active', true)->orderBy('name')->get();

        return view('distributor.coupons.create', compact('categories'));
    }

    /**
     * Store a newly created coupon.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'valid_for' => 'required|in:website,store,both',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'free_shipping' => 'boolean',
            'exclude_discounted' => 'boolean',
            'features' => 'nullable|array',
            'terms' => 'nullable|array',
            'commission_terms' => 'nullable|array',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category_id' => 'nullable|exists:categories,id',
            'sub_category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|max:2048',
            'promo_images' => 'nullable|array|max:5',
            'promo_images.*' => 'image|max:2048',
            'promo_video' => 'nullable|file|mimes:mp4,mov,avi|max:20480',
        ]);

        // Note: code is generated when the coupon is activated for a marketer, not here.

        // Handle main coupon image
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('coupons/images', 'public');
        }

        // Handle promo images
        $promoImages = [];
        if ($request->hasFile('promo_images')) {
            foreach ($request->file('promo_images') as $image) {
                $promoImages[] = $image->store('coupons/promo', 'public');
            }
        }
        $validated['promo_images'] = !empty($promoImages) ? json_encode($promoImages) : null;

        // Handle promo video
        if ($request->hasFile('promo_video')) {
            $validated['promo_video'] = $request->file('promo_video')->store('coupons/videos', 'public');
        }

        // Set the store_id and created_by to the authenticated user
        $validated['store_id'] = $user->id;
        $validated['created_by'] = $user->id;
        $validated['is_active'] = true;

        Coupon::create($validated);

        return redirect()->route('distributor.coupons.index')
            ->with('success', app()->getLocale() == 'ar' ? 'تم إنشاء الكوبون بنجاح' : 'Coupon created successfully');
    }

    /**
     * Display the specified coupon.
     */
    public function show(Coupon $coupon)
    {
        $user = auth()->user();
        
        // Verify ownership
        if ($coupon->store_id !== $user->id) {
            abort(403);
        }

        return view('distributor.coupons.show', compact('coupon'));
    }

    /**
     * Show the form for editing the specified coupon.
     */
    public function edit(Coupon $coupon)
    {
        $user = auth()->user();
        
        // Verify ownership
        if ($coupon->store_id !== $user->id) {
            abort(403);
        }

        return view('distributor.coupons.edit', compact('coupon'));
    }

    /**
     * Update the specified coupon.
     */
    public function update(Request $request, Coupon $coupon)
    {
        $user = auth()->user();
        
        // Verify ownership
        if ($coupon->store_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'valid_for' => 'required|in:website,store,both',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'free_shipping' => 'boolean',
            'exclude_discounted' => 'boolean',
            'features' => 'nullable|array',
            'terms' => 'nullable|array',
            'commission_terms' => 'nullable|array',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'promo_images' => 'nullable|array|max:5',
            'promo_images.*' => 'image|max:2048',
            'promo_video' => 'nullable|file|mimes:mp4,mov,avi|max:20480',
        ]);

        // Handle promo images
        if ($request->hasFile('promo_images')) {
            $promoImages = [];
            foreach ($request->file('promo_images') as $image) {
                $promoImages[] = $image->store('coupons/promo', 'public');
            }
            $validated['promo_images'] = !empty($promoImages) ? json_encode($promoImages) : null;
        }

        // Handle promo video
        if ($request->hasFile('promo_video')) {
            $validated['promo_video'] = $request->file('promo_video')->store('coupons/videos', 'public');
        }

        $coupon->update($validated);

        return redirect()->route('distributor.coupons.show', $coupon)
            ->with('success', app()->getLocale() == 'ar' ? 'تم تحديث الكوبون بنجاح' : 'Coupon updated successfully');
    }

    /**
     * Delete the specified coupon.
     */
    public function destroy(Coupon $coupon)
    {
        $user = auth()->user();
        
        // Verify ownership
        if ($coupon->store_id !== $user->id) {
            abort(403);
        }

        $coupon->delete();

        return redirect()->route('distributor.coupons.index')
            ->with('success', app()->getLocale() == 'ar' ? 'تم حذف الكوبون بنجاح' : 'Coupon deleted successfully');
    }

    /**
     * Toggle coupon status (active/inactive).
     */
    public function toggleStatus(Coupon $coupon)
    {
        $user = auth()->user();
        
        // Verify ownership
        if ($coupon->store_id !== $user->id) {
            abort(403);
        }

        $coupon->update(['is_active' => !$coupon->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $coupon->is_active,
            'message' => $coupon->is_active 
                ? (app()->getLocale() == 'ar' ? 'تم تفعيل الكوبون' : 'Coupon activated')
                : (app()->getLocale() == 'ar' ? 'تم تعطيل الكوبون' : 'Coupon deactivated')
        ]);
    }

    /**
     * Generate a unique coupon code.
     */
    public function generateCode()
    {
        $code = Coupon::generateCode();
        
        return response()->json([
            'code' => $code
        ]);
    }
}
