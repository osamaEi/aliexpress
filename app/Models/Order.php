<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_number',
        'aliexpress_order_id',
        'product_id',
        'coupon_id',
        'coupon_code',
        'quantity',
        'selected_sku_attr',
        'selected_variant_details',
        'unit_price',
        'total_price',
        'freight_amount',
        'total_amount',
        'aliexpress_profit',
        'seller_profit',
        'currency',
        'customer_name',
        'customer_email',
        'customer_phone',
        'phone_country',
        'shipping_address',
        'shipping_address2',
        'shipping_city',
        'shipping_province',
        'shipping_country',
        'shipping_zip',
        'status',
        'payment_status',
        'tracking_number',
        'shipping_method',
        'placed_at',
        'shipped_at',
        'delivered_at',
        'customer_notes',
        'admin_notes',
        'aliexpress_response',
        'discount_amount',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'freight_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'aliexpress_profit' => 'decimal:2',
        'seller_profit' => 'decimal:2',
        'quantity' => 'integer',
        'selected_variant_details' => 'array',
        'placed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'aliexpress_response' => 'array',
        'discount_amount' => 'decimal:2',
    ];

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product for this order.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the coupon used for this order.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the shipping information for this order.
     */
    public function shipping(): HasOne
    {
        return $this->hasOne(Shipping::class);
    }

    /**
     * Get the profit record for this order.
     */
    public function profit(): HasOne
    {
        return $this->hasOne(Profit::class);
    }

    /**
     * Scope a query to only include orders with a specific status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include placed orders.
     */
    public function scopePlaced($query)
    {
        return $query->where('status', 'placed');
    }

    /**
     * Scope a query to only include shipped orders.
     */
    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'placed' => 'primary',
            'paid' => 'success',
            'shipped' => 'info',
            'delivered' => 'success',
            'cancelled' => 'danger',
            'failed' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get status display name (bilingual)
     */
    public function getStatusName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        $statusNames = [
            'pending' => ['en' => 'Pending', 'ar' => 'قيد الانتظار'],
            'processing' => ['en' => 'Processing', 'ar' => 'قيد المعالجة'],
            'placed' => ['en' => 'Placed', 'ar' => 'تم التقديم'],
            'paid' => ['en' => 'Paid', 'ar' => 'مدفوع'],
            'shipped' => ['en' => 'Shipped', 'ar' => 'تم الشحن'],
            'delivered' => ['en' => 'Delivered', 'ar' => 'تم التسليم'],
            'cancelled' => ['en' => 'Cancelled', 'ar' => 'ملغي'],
            'failed' => ['en' => 'Failed', 'ar' => 'فشل'],
        ];

        return $statusNames[$this->status][$locale] ?? ucfirst($this->status);
    }

    /**
     * Get payment status badge color
     */
    public function getPaymentStatusBadgeColor(): string
    {
        return match($this->payment_status) {
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Get payment status display name (bilingual)
     */
    public function getPaymentStatusName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        $statusNames = [
            'pending' => ['en' => 'Pending', 'ar' => 'قيد الانتظار'],
            'paid' => ['en' => 'Paid', 'ar' => 'مدفوع'],
            'failed' => ['en' => 'Failed', 'ar' => 'فشل'],
            'refunded' => ['en' => 'Refunded', 'ar' => 'مسترد'],
        ];

        return $statusNames[$this->payment_status][$locale] ?? ucfirst($this->payment_status);
    }

    /**
     * Check if order can be sent to AliExpress
     */
    public function canBePlaced(): bool
    {
        return $this->status === 'pending' && $this->payment_status === 'paid';
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing', 'placed']);
    }

    /**
     * Get total profit from all sources
     */
    public function getTotalProfit(): float
    {
        return $this->aliexpress_profit + $this->seller_profit;
    }

    /**
     * Calculate and set all profit fields for this order
     */
    public function calculateProfits(): void
    {
        if (!$this->product) {
            $this->load('product');
        }

        $product = $this->product;

        if (!$product) {
            // No product found, set all profits to 0
            $this->aliexpress_profit = 0;
            $this->seller_profit = 0;
            return;
        }

        // 1. Calculate AliExpress Profit (supplier profit margin)
        $aliexpressProfit = 0;
        $aliexpressCost = ($product->aliexpress_price + ($product->shipping_cost ?? 0)) * $this->quantity;
        if ($product->aliexpress_price && $product->supplier_profit_margin) {
            // Calculate profit based on margin percentage
            $aliexpressProfit = $aliexpressCost * ($product->supplier_profit_margin / 100);
        }

        // 2. Calculate Seller Profit
        $sellerProfit = 0;
        if ($this->user_id && $product->category_id) {
            $sellerProfitSetting = SellerSubcategoryProfit::where('user_id', $this->user_id)
                ->where('category_id', $product->category_id)
                ->where('is_active', true)
                ->first();

            if ($sellerProfitSetting) {
                // Use the actual total_price from the order as base price
                $basePrice = $this->total_price ?? ($this->unit_price * $this->quantity);
                // Pass the order currency so fixed amounts get converted properly
                $sellerProfit = $sellerProfitSetting->calculateProfit($basePrice, $this->currency);
            }
        }

        // Update the profit fields
        $this->aliexpress_profit = round($aliexpressProfit, 2);
        $this->seller_profit = round($sellerProfit, 2);
    }

    /**
     * Map AliExpress order status to local status
     */
    public static function mapAliExpressStatus(string $aliexpressStatus): string
    {
        return match($aliexpressStatus) {
            'PLACE_ORDER_SUCCESS' => 'placed',
            'PAYMENT_PROCESSING' => 'processing',
            'WAIT_SELLER_EXAMINE_MONEY' => 'processing',
            'RISK_CONTROL' => 'processing',
            'RISK_CONTROL_HOLD' => 'processing',
            'WAIT_SELLER_SEND_GOODS' => 'placed',
            'SELLER_PART_SEND_GOODS' => 'shipped',
            'WAIT_BUYER_ACCEPT_GOODS' => 'shipped',
            'FINISH' => 'delivered',
            'IN_CANCEL' => 'cancelled',
            'WAIT_GROUP' => 'pending',
            'WAIT_COMPLETE_ADDRESS' => 'pending',
            default => 'processing',
        };
    }

    /**
     * Get AliExpress order status display name (multilingual)
     */
    public static function getAliExpressStatusDisplay(string $aliexpressStatus, string $locale = 'en'): string
    {
        $statusMap = [
            'PLACE_ORDER_SUCCESS' => [
                'en' => 'Order Placed Successfully',
                'ar' => 'تم تقديم الطلب بنجاح'
            ],
            'PAYMENT_PROCESSING' => [
                'en' => 'Payment Processing',
                'ar' => 'معالجة الدفع'
            ],
            'WAIT_SELLER_EXAMINE_MONEY' => [
                'en' => 'Waiting for Seller to Confirm Amount',
                'ar' => 'في انتظار تأكيد البائع للمبلغ'
            ],
            'RISK_CONTROL' => [
                'en' => 'Risk Control in Progress',
                'ar' => 'مراقبة المخاطر جارية'
            ],
            'RISK_CONTROL_HOLD' => [
                'en' => 'Ongoing Risk Control',
                'ar' => 'مراقبة المخاطر مستمرة'
            ],
            'WAIT_SELLER_SEND_GOODS' => [
                'en' => 'Waiting for Seller to Ship',
                'ar' => 'في انتظار شحن البائع'
            ],
            'SELLER_PART_SEND_GOODS' => [
                'en' => 'Partial Shipment',
                'ar' => 'شحن جزئي'
            ],
            'WAIT_BUYER_ACCEPT_GOODS' => [
                'en' => 'Waiting for Buyer to Receive Goods',
                'ar' => 'في انتظار استلام المشتري'
            ],
            'FINISH' => [
                'en' => 'Order Completed',
                'ar' => 'اكتمل الطلب'
            ],
            'IN_CANCEL' => [
                'en' => 'Cancel Order',
                'ar' => 'إلغاء الطلب'
            ],
            'WAIT_GROUP' => [
                'en' => 'Waiting for Group Formation',
                'ar' => 'في انتظار تشكيل المجموعة'
            ],
            'WAIT_COMPLETE_ADDRESS' => [
                'en' => 'Waiting to Supplement Shipping Address',
                'ar' => 'في انتظار استكمال عنوان الشحن'
            ],
        ];

        return $statusMap[$aliexpressStatus][$locale] ?? $aliexpressStatus;
    }

    /**
     * Get AliExpress logistics status display name
     */
    public static function getLogisticsStatusDisplay(string $logisticsStatus, string $locale = 'en'): string
    {
        $statusMap = [
            'NO_LOGISTICS' => [
                'en' => 'Not Shipped',
                'ar' => 'لم يتم الشحن'
            ],
            'SELLER_SEND_GOODS' => [
                'en' => 'Shipped',
                'ar' => 'تم الشحن'
            ],
            'BUYER_ACCEPT_GOODS' => [
                'en' => 'Delivered',
                'ar' => 'تم التسليم'
            ],
        ];

        return $statusMap[$logisticsStatus][$locale] ?? $logisticsStatus;
    }

    /**
     * Update order from AliExpress API response
     */
    public function updateFromAliExpressResponse(array $responseData): void
    {
        $result = $responseData['result'] ?? $responseData;

        // Map and update order status
        if (isset($result['order_status'])) {
            $newStatus = self::mapAliExpressStatus($result['order_status']);

            // Only update if status changed
            if ($this->status !== $newStatus) {
                $this->status = $newStatus;
            }
        }

        // Update logistics status and tracking
        if (isset($result['logistics_status'])) {
            $logisticsStatus = $result['logistics_status'];

            // Update shipped_at if seller sent goods
            if ($logisticsStatus === 'SELLER_SEND_GOODS' && !$this->shipped_at) {
                $this->shipped_at = now();
                if ($this->status !== 'delivered') {
                    $this->status = 'shipped';
                }
            }

            // Update delivered_at if buyer accepted goods
            if ($logisticsStatus === 'BUYER_ACCEPT_GOODS' && !$this->delivered_at) {
                $this->delivered_at = now();
                $this->status = 'delivered';
            }
        }

        // Update tracking number from logistics info
        if (isset($result['logistics_info_list']['aeop_order_logistics_info'][0]['logistics_no'])) {
            $this->tracking_number = $result['logistics_info_list']['aeop_order_logistics_info'][0]['logistics_no'];
        }

        // Update shipping method
        if (isset($result['logistics_info_list']['aeop_order_logistics_info'][0]['logistics_service'])) {
            $this->shipping_method = $result['logistics_info_list']['aeop_order_logistics_info'][0]['logistics_service'];
        }

        // Store full response
        $this->aliexpress_response = $responseData;

        $this->save();
    }

    /**
     * Get AliExpress order status from stored response
     */
    public function getAliExpressOrderStatus(): ?string
    {
        if (!$this->aliexpress_response) {
            return null;
        }

        return $this->aliexpress_response['result']['order_status'] ?? null;
    }

    /**
     * Get AliExpress logistics status from stored response
     */
    public function getAliExpressLogisticsStatus(): ?string
    {
        if (!$this->aliexpress_response) {
            return null;
        }

        return $this->aliexpress_response['result']['logistics_status'] ?? null;
    }

    /**
     * Get formatted status badge with icon
     */
    public function getStatusBadgeHtml(string $locale = 'en'): string
    {
        $aliexpressStatus = $this->getAliExpressOrderStatus();
        $statusName = $aliexpressStatus
            ? self::getAliExpressStatusDisplay($aliexpressStatus, $locale)
            : $this->getStatusName();

        $badgeColor = $this->getStatusBadgeColor();
        $icon = $this->getStatusIcon();

        return sprintf(
            '<span class="badge bg-%s"><i class="%s me-1"></i>%s</span>',
            $badgeColor,
            $icon,
            $statusName
        );
    }

    /**
     * Get status icon
     */
    public function getStatusIcon(): string
    {
        return match($this->status) {
            'pending' => 'ri-time-line',
            'processing' => 'ri-loader-4-line',
            'placed' => 'ri-check-line',
            'paid' => 'ri-money-dollar-circle-line',
            'shipped' => 'ri-ship-line',
            'delivered' => 'ri-checkbox-circle-line',
            'cancelled' => 'ri-close-circle-line',
            'failed' => 'ri-error-warning-line',
            default => 'ri-information-line',
        };
    }

    /**
     * Get full status display with all information
     */
    public function getFullStatusDisplay(string $locale = 'en'): array
    {
        $aliexpressOrderStatus = $this->getAliExpressOrderStatus();
        $aliexpressLogisticsStatus = $this->getAliExpressLogisticsStatus();

        return [
            'local_status' => $this->status,
            'local_status_name' => $this->getStatusName(),
            'local_status_color' => $this->getStatusBadgeColor(),
            'local_status_icon' => $this->getStatusIcon(),
            'aliexpress_order_status' => $aliexpressOrderStatus,
            'aliexpress_order_status_name' => $aliexpressOrderStatus
                ? self::getAliExpressStatusDisplay($aliexpressOrderStatus, $locale)
                : null,
            'aliexpress_logistics_status' => $aliexpressLogisticsStatus,
            'aliexpress_logistics_status_name' => $aliexpressLogisticsStatus
                ? self::getLogisticsStatusDisplay($aliexpressLogisticsStatus, $locale)
                : null,
            'tracking_number' => $this->tracking_number,
            'shipping_method' => $this->shipping_method,
            'placed_at' => $this->placed_at,
            'shipped_at' => $this->shipped_at,
            'delivered_at' => $this->delivered_at,
        ];
    }
}
