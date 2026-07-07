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

        // Pending activation-request counts per coupon (for the requests button badge)
        $pendingCounts = \DB::table('coupon_marketer')
            ->whereIn('coupon_id', $coupons->pluck('id'))
            ->where('status', 'pending')
            ->select('coupon_id', \DB::raw('COUNT(*) as cnt'))
            ->groupBy('coupon_id')
            ->pluck('cnt', 'coupon_id');

        return view('distributor.coupons.index', compact('coupons', 'pendingCounts'));
    }

    /**
     * Show the form for creating a new coupon.
     */
    public function create()
    {
        $categories = \App\Models\Category::whereNull('parent_id')->where('is_active', true)->orderBy('name')->get();

        // Products assigned to this distributor — the coupon can be scoped to these.
        $products = auth()->user()->assignedProducts()
            ->where('products.is_active', true)
            ->orderBy('products.name')
            ->get(['products.id', 'products.name', 'products.name_ar']);

        return view('distributor.coupons.create', compact('categories', 'products'));
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
            'coupon_method' => 'required|in:code,link',
            'direct_link' => 'nullable|required_if:coupon_method,link|url|max:2000',
            'category_id' => 'nullable|exists:categories,id',
            'sub_category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|max:2048',
            'promo_images' => 'nullable|array|max:5',
            'promo_images.*' => 'image|max:2048',
            'promo_video' => 'nullable|file|mimes:mp4,mov,avi|max:20480',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
        ], [
            'product_ids.required' => app()->getLocale() == 'ar' ? 'اختر منتجًا واحدًا على الأقل يطبّق عليه الكوبون.' : 'Select at least one product the coupon applies to.',
            'product_ids.min' => app()->getLocale() == 'ar' ? 'اختر منتجًا واحدًا على الأقل يطبّق عليه الكوبون.' : 'Select at least one product the coupon applies to.',
        ]);

        // Restrict the chosen products to the ones actually assigned to this distributor.
        $allowedProductIds = $user->assignedProducts()->pluck('products.id')->all();
        $productIds = array_values(array_intersect($request->input('product_ids', []), $allowedProductIds));

        if (empty($productIds)) {
            return back()->withInput()->withErrors([
                'product_ids' => app()->getLocale() == 'ar'
                    ? 'يجب اختيار منتجات من متجرك فقط.'
                    : 'You may only select products from your own store.',
            ]);
        }

        // product_ids is a relation, not a column — keep it out of mass assignment.
        unset($validated['product_ids']);

        // Note: code is generated when the coupon is activated for a marketer, not here.

        // Handle main image
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

        $coupon = Coupon::create($validated);
        $coupon->products()->sync($productIds);

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

    /**
     * List marketers who requested activation of the given coupon, so the
     * distributor can approve (with a code) or reject each request.
     */
    public function activationRequests(Coupon $coupon)
    {
        $user = auth()->user();

        // Verify ownership
        if ($coupon->store_id !== $user->id) {
            abort(403);
        }

        $requests = \DB::table('coupon_marketer')
            ->join('users', 'users.id', '=', 'coupon_marketer.user_id')
            ->where('coupon_marketer.coupon_id', $coupon->id)
            ->orderByRaw("FIELD(coupon_marketer.status, 'pending', 'active', 'rejected')")
            ->orderBy('coupon_marketer.created_at', 'desc')
            ->get([
                'coupon_marketer.user_id',
                'coupon_marketer.status',
                'coupon_marketer.tracking_code',
                'coupon_marketer.earnings',
                'coupon_marketer.created_at',
                'users.name as marketer_name',
                'users.email as marketer_email',
            ]);

        return view('distributor.coupons.activation-requests', compact('coupon', 'requests'));
    }

    /**
     * Approve a marketer's activation request by assigning them a code.
     * The code is suggested automatically but editable by the distributor.
     */
    public function approveActivation(Request $request, Coupon $coupon, \App\Models\User $marketer)
    {
        $user = auth()->user();

        // Verify ownership
        if ($coupon->store_id !== $user->id) {
            abort(403);
        }

        $ar = app()->getLocale() === 'ar';

        // The pending request must exist
        $pivot = \DB::table('coupon_marketer')
            ->where('coupon_id', $coupon->id)
            ->where('user_id', $marketer->id)
            ->first();

        if (!$pivot || $pivot->status !== 'pending') {
            return back()->with('error', $ar ? 'لا يوجد طلب معلّق لهذا المسوّق.' : 'No pending request for this marketer.');
        }

        $validated = $request->validate([
            'code' => [
                'required', 'string', 'max:30',
                \Illuminate\Validation\Rule::unique('coupon_marketer', 'tracking_code'),
            ],
        ], [], [
            'code' => $ar ? 'الكود' : 'code',
        ]);

        // Find the linked activation ticket (if any) so it gets closed and the
        // marketer is notified inside the thread.
        $ticket = \App\Models\Ticket::where('coupon_id', $coupon->id)
            ->where('user_id', $marketer->id)
            ->latest()
            ->first();

        if ($ticket) {
            \App\Http\Controllers\MarketerController::applyCouponDecision($ticket, 'approve', $validated['code']);
        } else {
            // No ticket: update the pivot directly.
            \DB::table('coupon_marketer')
                ->where('coupon_id', $coupon->id)
                ->where('user_id', $marketer->id)
                ->update([
                    'status' => 'active',
                    'tracking_code' => $validated['code'],
                    'updated_at' => now(),
                ]);
        }

        return redirect()->route('distributor.coupons.activation-requests', $coupon)
            ->with('success', $ar ? 'تم تفعيل الكوبون للمسوّق وإرسال الكود.' : 'Coupon activated and code sent to the marketer.');
    }

    /**
     * Reject a marketer's activation request.
     */
    public function rejectActivation(Coupon $coupon, \App\Models\User $marketer)
    {
        $user = auth()->user();

        // Verify ownership
        if ($coupon->store_id !== $user->id) {
            abort(403);
        }

        $ar = app()->getLocale() === 'ar';

        $pivot = \DB::table('coupon_marketer')
            ->where('coupon_id', $coupon->id)
            ->where('user_id', $marketer->id)
            ->first();

        if (!$pivot || $pivot->status !== 'pending') {
            return back()->with('error', $ar ? 'لا يوجد طلب معلّق لهذا المسوّق.' : 'No pending request for this marketer.');
        }

        $ticket = \App\Models\Ticket::where('coupon_id', $coupon->id)
            ->where('user_id', $marketer->id)
            ->latest()
            ->first();

        if ($ticket) {
            \App\Http\Controllers\MarketerController::applyCouponDecision($ticket, 'reject');
        } else {
            \DB::table('coupon_marketer')
                ->where('coupon_id', $coupon->id)
                ->where('user_id', $marketer->id)
                ->update(['status' => 'rejected', 'updated_at' => now()]);
        }

        return redirect()->route('distributor.coupons.activation-requests', $coupon)
            ->with('success', $ar ? 'تم رفض طلب التفعيل.' : 'Activation request rejected.');
    }

    /**
     * Edit the code assigned to an already-active marketer request.
     */
    public function updateActivation(Request $request, Coupon $coupon, \App\Models\User $marketer)
    {
        $user = auth()->user();

        // Verify ownership
        if ($coupon->store_id !== $user->id) {
            abort(403);
        }

        $ar = app()->getLocale() === 'ar';

        $pivot = \DB::table('coupon_marketer')
            ->where('coupon_id', $coupon->id)
            ->where('user_id', $marketer->id)
            ->first();

        if (!$pivot || $pivot->status !== 'active') {
            return back()->with('error', $ar ? 'لا يوجد طلب مفعّل لهذا المسوّق.' : 'No active request for this marketer.');
        }

        $validated = $request->validate([
            'code' => [
                'required', 'string', 'max:30',
                \Illuminate\Validation\Rule::unique('coupon_marketer', 'tracking_code')
                    ->ignore($pivot->id),
            ],
        ], [], [
            'code' => $ar ? 'الكود' : 'code',
        ]);

        \DB::table('coupon_marketer')
            ->where('coupon_id', $coupon->id)
            ->where('user_id', $marketer->id)
            ->update([
                'tracking_code' => $validated['code'],
                'updated_at' => now(),
            ]);

        return redirect()->route('distributor.coupons.activation-requests', $coupon)
            ->with('success', $ar ? 'تم تعديل كود المسوّق.' : 'Marketer code updated.');
    }

    /**
     * Delete a marketer's request row entirely (any status). This frees the
     * marketer to request activation again.
     */
    public function deleteActivation(Coupon $coupon, \App\Models\User $marketer)
    {
        $user = auth()->user();

        // Verify ownership
        if ($coupon->store_id !== $user->id) {
            abort(403);
        }

        $ar = app()->getLocale() === 'ar';

        $deleted = \DB::table('coupon_marketer')
            ->where('coupon_id', $coupon->id)
            ->where('user_id', $marketer->id)
            ->delete();

        return redirect()->route('distributor.coupons.activation-requests', $coupon)
            ->with('success', $deleted
                ? ($ar ? 'تم حذف الطلب.' : 'Request deleted.')
                : ($ar ? 'الطلب غير موجود.' : 'Request not found.'));
    }
}
