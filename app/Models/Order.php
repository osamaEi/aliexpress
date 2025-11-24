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
        'quantity',
        'selected_sku_attr',
        'selected_variant_details',
        'unit_price',
        'total_price',
        'freight_amount',
        'total_amount',
        'aliexpress_profit',
        'admin_category_profit',
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
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'freight_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'aliexpress_profit' => 'decimal:2',
        'admin_category_profit' => 'decimal:2',
        'seller_profit' => 'decimal:2',
        'quantity' => 'integer',
        'selected_variant_details' => 'array',
        'placed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'aliexpress_response' => 'array',
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
     * Get status display name
     */
    public function getStatusName(): string
    {
        return ucfirst($this->status);
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
     * Get payment status display name
     */
    public function getPaymentStatusName(): string
    {
        return ucfirst($this->payment_status);
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
        return $this->aliexpress_profit + $this->admin_category_profit + $this->seller_profit;
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
            $this->admin_category_profit = 0;
            $this->seller_profit = 0;
            return;
        }

        // 1. Calculate AliExpress Profit (supplier profit margin)
        $aliexpressProfit = 0;
        if ($product->aliexpress_price && $product->supplier_profit_margin) {
            // AliExpress cost = (product price + shipping) * quantity
            $aliexpressCost = ($product->aliexpress_price + ($product->shipping_cost ?? 0)) * $this->quantity;

            // Calculate profit based on margin percentage
            $aliexpressProfit = $aliexpressCost * ($product->supplier_profit_margin / 100);
        }

        // 2. Calculate Admin Category Profit (per unit * quantity)
        $adminCategoryProfit = 0;
        if ($product->category_id) {
            $adminProfitPerUnit = AdminCategoryProfit::getProfitForCategory($product->category_id);
            $adminCategoryProfit = $adminProfitPerUnit * $this->quantity;
        }

        // 3. Calculate Seller Profit
        $sellerProfit = 0;
        if ($this->user_id && $product->category_id) {
            $sellerProfitSetting = SellerSubcategoryProfit::where('user_id', $this->user_id)
                ->where('category_id', $product->category_id)
                ->where('is_active', true)
                ->first();

            if ($sellerProfitSetting) {
                // Use the actual total_price from the order as base price
                $basePrice = $this->total_price ?? ($this->unit_price * $this->quantity);
                $sellerProfit = $sellerProfitSetting->calculateProfit($basePrice);
            }
        }

        // Update the profit fields
        $this->aliexpress_profit = round($aliexpressProfit, 2);
        $this->admin_category_profit = round($adminCategoryProfit, 2);
        $this->seller_profit = round($sellerProfit, 2);
    }
}
