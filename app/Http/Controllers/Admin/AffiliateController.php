<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AffiliateController extends Controller
{
    /**
     * Display stores list (distributors with coupons).
     */
    public function stores(Request $request)
    {
        // Get all distributors (both with accounts and without)
        $query = User::where('user_type', 'distributor');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('store_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        $stores = $query->withCount([
            'coupons',
            'coupons as active_coupons_count' => function ($q) {
                $q->active();
            },
            'coupons as expired_coupons_count' => function ($q) {
                $q->expired();
            },
        ])
        ->withSum('coupons', 'total_commission_earned')
        ->latest()
        ->paginate(20);

        // Calculate pending commissions for each store
        foreach ($stores as $store) {
            $store->pending_commission = CouponUsage::whereHas('coupon', function ($q) use ($store) {
                $q->where('store_id', $store->id);
            })->pending()->sum('commission_amount');
        }

        // Get countries for filter
        $countries = User::where('user_type', 'distributor')
            ->whereNotNull('country')
            ->distinct()
            ->pluck('country');

        return view('admin.affiliate.stores', compact('stores', 'countries'));
    }

    /**
     * Show form to create a new store (distributor without login).
     */
    public function createStore()
    {
        $countries = [
            'AE' => ['ar' => 'الإمارات', 'en' => 'UAE'],
            'SA' => ['ar' => 'السعودية', 'en' => 'Saudi Arabia'],
            'KW' => ['ar' => 'الكويت', 'en' => 'Kuwait'],
            'QA' => ['ar' => 'قطر', 'en' => 'Qatar'],
            'BH' => ['ar' => 'البحرين', 'en' => 'Bahrain'],
            'OM' => ['ar' => 'عمان', 'en' => 'Oman'],
            'EG' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'JO' => ['ar' => 'الأردن', 'en' => 'Jordan'],
            'LB' => ['ar' => 'لبنان', 'en' => 'Lebanon'],
        ];

        return view('admin.affiliate.create-store', compact('countries'));
    }

    /**
     * Store a new distributor (without login credentials).
     */
    public function storeStore(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:2',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ]);

        // Create user without password (no login capability)
        $user = User::create([
            'name' => $validated['name'],
            'store_name' => $validated['store_name'],
            'country' => $validated['country'],
            'phone' => $validated['phone'] ?? null,
            'user_type' => 'distributor',
            'email' => 'store_' . Str::random(10) . '@placeholder.local', // Placeholder email
            'password' => '', // Empty password - cannot login
            'is_verified' => true,
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);
        }

        return redirect()->route('admin.affiliate.stores')
            ->with('success', app()->getLocale() == 'ar' ? 'تم إضافة المتجر بنجاح' : 'Store added successfully');
    }

    /**
     * Show store details with coupons.
     */
    public function showStore(User $store)
    {
        $store->load(['coupons' => function ($q) {
            $q->latest();
        }]);

        $stats = [
            'total_coupons' => $store->coupons->count(),
            'active_coupons' => $store->coupons->filter(fn($c) => $c->isValid())->count(),
            'expired_coupons' => $store->coupons->filter(fn($c) => $c->isExpired())->count(),
            'total_earnings' => $store->coupons->sum('total_commission_earned'),
            'pending_commission' => CouponUsage::whereHas('coupon', function ($q) use ($store) {
                $q->where('store_id', $store->id);
            })->pending()->sum('commission_amount'),
        ];

        return view('admin.affiliate.show-store', compact('store', 'stats'));
    }

    /**
     * Display active coupons.
     */
    public function activeCoupons(Request $request)
    {
        $query = Coupon::with(['store', 'creator'])
            ->active();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title_ar', 'like', "%{$search}%")
                  ->orWhere('title_en', 'like', "%{$search}%");
            });
        }

        // Filter by store
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        $coupons = $query->latest()->paginate(20);
        $stores = User::where('user_type', 'distributor')->get(['id', 'store_name', 'name']);

        return view('admin.affiliate.coupons-active', compact('coupons', 'stores'));
    }

    /**
     * Display expired coupons.
     */
    public function expiredCoupons(Request $request)
    {
        $query = Coupon::with(['store', 'creator'])
            ->expired();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title_ar', 'like', "%{$search}%")
                  ->orWhere('title_en', 'like', "%{$search}%");
            });
        }

        // Filter by store
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        $coupons = $query->latest()->paginate(20);
        $stores = User::where('user_type', 'distributor')->get(['id', 'store_name', 'name']);

        return view('admin.affiliate.coupons-expired', compact('coupons', 'stores'));
    }

    /**
     * Show form to create a new coupon.
     */
    public function createCoupon()
    {
        $stores = User::where('user_type', 'distributor')->get(['id', 'store_name', 'name', 'country']);

        return view('admin.affiliate.create-coupon', compact('stores'));
    }

    /**
     * Store a new coupon.
     */
    public function storeCoupon(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:users,id',
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
            'code' => 'nullable|string|max:10|unique:coupons,code',
            'promo_images' => 'nullable|array|max:5',
            'promo_images.*' => 'image|max:2048',
            'promo_video' => 'nullable|file|mimes:mp4,mov,avi|max:20480',
        ]);

        // Generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = Coupon::generateCode();
        }

        // Handle promo images
        $promoImages = [];
        if ($request->hasFile('promo_images')) {
            foreach ($request->file('promo_images') as $image) {
                $path = $image->store('coupons/images', 'public');
                $promoImages[] = $path;
            }
        }

        // Handle promo video
        $promoVideo = null;
        if ($request->hasFile('promo_video')) {
            $promoVideo = $request->file('promo_video')->store('coupons/videos', 'public');
        }

        $coupon = Coupon::create([
            ...$validated,
            'created_by' => auth()->id(),
            'promo_images' => $promoImages,
            'promo_video' => $promoVideo,
            'free_shipping' => $request->boolean('free_shipping'),
            'exclude_discounted' => $request->boolean('exclude_discounted'),
        ]);

        return redirect()->route('admin.affiliate.coupons.active')
            ->with('success', app()->getLocale() == 'ar' ? 'تم إضافة الكوبون بنجاح' : 'Coupon added successfully');
    }

    /**
     * Show coupon details.
     */
    public function showCoupon(Coupon $coupon)
    {
        $coupon->load(['store', 'creator', 'usages.user']);

        $stats = [
            'total_uses' => $coupon->used_count,
            'total_discount' => $coupon->total_discount_given,
            'total_commission' => $coupon->total_commission_earned,
            'pending_commission' => $coupon->usages()->pending()->sum('commission_amount'),
        ];

        return view('admin.affiliate.show-coupon', compact('coupon', 'stats'));
    }

    /**
     * Edit coupon form.
     */
    public function editCoupon(Coupon $coupon)
    {
        $stores = User::where('user_type', 'distributor')->get(['id', 'store_name', 'name', 'country']);

        return view('admin.affiliate.edit-coupon', compact('coupon', 'stores'));
    }

    /**
     * Update coupon.
     */
    public function updateCoupon(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:users,id',
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
            'is_active' => 'boolean',
            'promo_images' => 'nullable|array|max:5',
            'promo_images.*' => 'image|max:2048',
            'promo_video' => 'nullable|file|mimes:mp4,mov,avi|max:20480',
        ]);

        // Handle promo images
        if ($request->hasFile('promo_images')) {
            // Delete old images
            if ($coupon->promo_images) {
                foreach ($coupon->promo_images as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }

            $promoImages = [];
            foreach ($request->file('promo_images') as $image) {
                $path = $image->store('coupons/images', 'public');
                $promoImages[] = $path;
            }
            $validated['promo_images'] = $promoImages;
        }

        // Handle promo video
        if ($request->hasFile('promo_video')) {
            // Delete old video
            if ($coupon->promo_video) {
                Storage::disk('public')->delete($coupon->promo_video);
            }
            $validated['promo_video'] = $request->file('promo_video')->store('coupons/videos', 'public');
        }

        $coupon->update([
            ...$validated,
            'free_shipping' => $request->boolean('free_shipping'),
            'exclude_discounted' => $request->boolean('exclude_discounted'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.affiliate.coupons.show', $coupon)
            ->with('success', app()->getLocale() == 'ar' ? 'تم تحديث الكوبون بنجاح' : 'Coupon updated successfully');
    }

    /**
     * Delete coupon.
     */
    public function destroyCoupon(Coupon $coupon)
    {
        // Delete media
        if ($coupon->promo_images) {
            foreach ($coupon->promo_images as $image) {
                Storage::disk('public')->delete($image);
            }
        }
        if ($coupon->promo_video) {
            Storage::disk('public')->delete($coupon->promo_video);
        }

        $coupon->delete();

        return redirect()->route('admin.affiliate.coupons.active')
            ->with('success', app()->getLocale() == 'ar' ? 'تم حذف الكوبون بنجاح' : 'Coupon deleted successfully');
    }

    /**
     * Generate unique coupon code (AJAX).
     */
    public function generateCode()
    {
        return response()->json([
            'code' => Coupon::generateCode()
        ]);
    }

    /**
     * Toggle coupon active status.
     */
    public function toggleCouponStatus(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $coupon->is_active,
            'message' => $coupon->is_active
                ? (app()->getLocale() == 'ar' ? 'تم تفعيل الكوبون' : 'Coupon activated')
                : (app()->getLocale() == 'ar' ? 'تم إلغاء تفعيل الكوبون' : 'Coupon deactivated')
        ]);
    }
}
