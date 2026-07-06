<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketerController extends Controller
{
    /**
     * Marketer dashboard with quick stats.
     */
    public function dashboard()
    {
        $user = Auth::user();

        $assigned = $user->assignedCoupons();

        $stats = [
            'active_coupons'    => (clone $assigned)->wherePivot('status', 'active')->count(),
            'pending_coupons'   => (clone $assigned)->wherePivot('status', 'pending')->count(),
            'wallet_balance'    => $user->wallet ? $user->wallet->balance : 0,
            'total_earnings'    => DB::table('coupon_marketer')->where('user_id', $user->id)->sum('earnings'),
            'pending_earnings'  => CouponUsage::where('marketer_id', $user->id)->where('commission_status', 'pending')->sum('commission_amount'),
        ];

        $recentCoupons = $user->assignedCoupons()
            ->orderByPivot('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('marketer.dashboard', compact('stats', 'recentCoupons'));
    }

    /**
     * Available coupons the marketer can promote (active store + global coupons),
     * plus the marketer's own activation status for each.
     */
    public function coupons(Request $request)
    {
        $user = Auth::user();

        $query = Coupon::with('store')
            ->where('is_active', true)
            ->where('end_date', '>=', now());

        // Filter by store type: global (admin) coupons vs. distributor store coupons
        $type = $request->get('type'); // 'global' | 'store' | null (all)
        if ($type === 'global') {
            $query->where('is_global', true);
        } elseif ($type === 'store') {
            $query->where('is_global', false);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_ar', 'like', "%{$search}%")
                  ->orWhere('title_en', 'like', "%{$search}%");
            });
        }

        $coupons = $query->latest()->paginate(12)->withQueryString();

        // Map this marketer's pivot rows by coupon id (status + tracking code)
        $myActivations = DB::table('coupon_marketer')
            ->where('user_id', $user->id)
            ->get(['coupon_id', 'status', 'tracking_code'])
            ->keyBy('coupon_id');

        return view('marketer.coupons.index', compact('coupons', 'myActivations'));
    }

    /**
     * Request activation of a coupon: creates a pending pivot row and a support
     * ticket routed to the coupon owner (admin for global coupons, the distributor
     * otherwise).
     */
    public function requestActivation(Request $request, Coupon $coupon)
    {
        $user = Auth::user();
        $ar = app()->getLocale() === 'ar';

        // Link coupons don't require activation — they're used via the direct link.
        if ($coupon->coupon_method === 'link') {
            return back()->with('error', $ar ? 'كوبونات الرابط المباشر لا تتطلب تفعيلاً.' : 'Direct-link coupons do not require activation.');
        }

        // Already requested / active?
        $existing = DB::table('coupon_marketer')
            ->where('coupon_id', $coupon->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return back()->with('error', $ar ? 'لديك طلب لهذا الكوبون بالفعل.' : 'You already have a request for this coupon.');
        }

        DB::transaction(function () use ($coupon, $user, $ar) {
            // Pending activation row
            DB::table('coupon_marketer')->insert([
                'coupon_id' => $coupon->id,
                'user_id' => $user->id,
                'status' => 'pending',
                'earnings' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Route the ticket: global coupon -> admin, otherwise -> owning distributor
            $isGlobal = (bool) $coupon->is_global;
            $couponTitle = $coupon->title_ar ?: $coupon->title_en;

            Ticket::create([
                'user_id' => $user->id,
                'coupon_id' => $coupon->id,
                'recipient_type' => $isGlobal ? 'admin' : 'distributor',
                'recipient_id' => $isGlobal ? null : $coupon->store_id,
                'subject' => ($ar ? 'طلب تفعيل كوبون: ' : 'Coupon activation request: ') . $couponTitle,
                'description' => $ar
                    ? "يطلب المسوّق ({$user->name}) تفعيل الكوبون \"{$couponTitle}\" للترويج له."
                    : "Marketer ({$user->name}) requests activation of coupon \"{$couponTitle}\".",
                'status' => 'open',
                'priority' => 'medium',
            ]);
        });

        return back()->with('success', $ar
            ? 'تم إرسال طلب التفعيل. سيتم إشعارك عند الموافقة.'
            : 'Activation request sent. You will be notified upon approval.');
    }

    /**
     * Apply an approve/reject decision to a coupon-activation ticket. Shared by the
     * distributor and admin ticket controllers. On approval, the marketer's pivot row
     * becomes active and gets a unique tracking code; on rejection it is marked rejected.
     */
    public static function applyCouponDecision(Ticket $ticket, string $decision, ?string $customCode = null): void
    {
        $marketerId = $ticket->user_id;
        $couponId = $ticket->coupon_id;
        $ar = app()->getLocale() === 'ar';

        DB::transaction(function () use ($ticket, $decision, $marketerId, $couponId, $ar, $customCode) {
            if ($decision === 'approve') {
                $trackingCode = $customCode ?: (strtoupper(Str::random(4)) . $couponId . strtoupper(Str::random(4)));

                DB::table('coupon_marketer')
                    ->where('coupon_id', $couponId)
                    ->where('user_id', $marketerId)
                    ->update([
                        'status' => 'active',
                        'tracking_code' => $trackingCode,
                        'updated_at' => now(),
                    ]);

                $replyMsg = $ar
                    ? "تمت الموافقة! رمز التتبع الخاص بك: {$trackingCode}"
                    : "Approved! Your tracking code: {$trackingCode}";
            } else {
                DB::table('coupon_marketer')
                    ->where('coupon_id', $couponId)
                    ->where('user_id', $marketerId)
                    ->update(['status' => 'rejected', 'updated_at' => now()]);

                $replyMsg = $ar ? 'تم رفض طلب التفعيل.' : 'Activation request rejected.';
            }

            // Notify the marketer inside the ticket thread, then close it
            \App\Models\TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => $replyMsg,
                'is_admin' => auth()->user()->user_type === 'admin',
            ]);

            $ticket->update(['status' => 'closed', 'closed_at' => now()]);
        });
    }

    /**
     * The marketer's own ACTIVE coupons (activation approved). Shows only coupons
     * whose activation request was approved, with the marketer's tracking code.
     */
    public function products(Request $request)
    {
        $user = Auth::user();

        $query = $user->assignedCoupons()
            ->wherePivot('status', 'active')
            ->with('store');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_ar', 'like', "%{$search}%")
                  ->orWhere('title_en', 'like', "%{$search}%");
            });
        }

        $coupons = $query->orderByPivot('created_at', 'desc')->paginate(12)->withQueryString();

        return view('marketer.products', compact('coupons'));
    }

    /**
     * Earnings / usage reports for the marketer.
     */
    public function reports()
    {
        $user = Auth::user();

        $usages = CouponUsage::where('marketer_id', $user->id)
            ->with('coupon')
            ->latest()
            ->paginate(20);

        $totals = [
            'total_commission'   => CouponUsage::where('marketer_id', $user->id)->sum('commission_amount'),
            'paid_commission'    => CouponUsage::where('marketer_id', $user->id)->where('commission_status', 'paid')->sum('commission_amount'),
            'pending_commission' => CouponUsage::where('marketer_id', $user->id)->where('commission_status', 'pending')->sum('commission_amount'),
            'total_uses'         => CouponUsage::where('marketer_id', $user->id)->count(),
        ];

        return view('marketer.reports', compact('usages', 'totals'));
    }
}
