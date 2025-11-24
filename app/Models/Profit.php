<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profit extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'aliexpress_price',
        'admin_profit',
        'seller_profit',
        'shipping_price',
        'total_cost',
        'total_profit',
        'final_price',
        'currency',
        'quantity',
    ];

    protected $casts = [
        'aliexpress_price' => 'decimal:2',
        'admin_profit' => 'decimal:2',
        'seller_profit' => 'decimal:2',
        'shipping_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'final_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the order that owns the profit record.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product that owns the profit record.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate and create profit record from order
     */
    public static function createFromOrder(Order $order): self
    {
        $product = $order->product;
        $quantity = $order->quantity;

        // Get AliExpress price from product
        $aliexpressPrice = $product->aliexpress_price ?? $product->price;

        // Get shipping price from order (freight_amount field if exists)
        $shippingPrice = $order->freight_amount ?? 0;

        // Calculate admin profit (from category profit settings)
        $adminProfit = 0;
        if ($product->category_id) {
            $categoryProfit = \App\Models\CategoryProfit::where('category_id', $product->category_id)
                ->where('is_active', true)
                ->first();

            if ($categoryProfit) {
                if ($categoryProfit->profit_type === 'percentage') {
                    $adminProfit = ($aliexpressPrice * $categoryProfit->profit_value / 100) * $quantity;
                } else {
                    $adminProfit = $categoryProfit->profit_value * $quantity;
                }
            }
        }

        // Calculate seller profit (from seller profit settings)
        $sellerProfit = 0;
        if ($order->user_id && $product->category_id) {
            $sellerProfitSetting = \App\Models\SellerSubcategoryProfit::where('user_id', $order->user_id)
                ->where('subcategory_id', $product->category_id)
                ->where('is_active', true)
                ->first();

            if ($sellerProfitSetting) {
                if ($sellerProfitSetting->profit_type === 'percentage') {
                    $sellerProfit = ($aliexpressPrice * $sellerProfitSetting->profit_value / 100) * $quantity;
                } else {
                    $sellerProfit = $sellerProfitSetting->profit_value * $quantity;
                }
            }
        }

        // Calculate totals
        $totalCost = ($aliexpressPrice * $quantity) + $shippingPrice;
        $totalProfit = $adminProfit + $sellerProfit;
        $finalPrice = $totalCost + $totalProfit;

        return self::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'aliexpress_price' => $aliexpressPrice,
            'admin_profit' => $adminProfit,
            'seller_profit' => $sellerProfit,
            'shipping_price' => $shippingPrice,
            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'final_price' => $finalPrice,
            'currency' => $order->currency ?? 'USD',
            'quantity' => $quantity,
        ]);
    }
}
