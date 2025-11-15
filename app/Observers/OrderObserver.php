<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "creating" event.
     * This runs before the order is saved to the database
     */
    public function creating(Order $order): void
    {
        // Calculate profits before saving
        $order->calculateProfits();

        Log::info('Order profits calculated on creation', [
            'order_number' => $order->order_number,
            'aliexpress_profit' => $order->aliexpress_profit,
            'admin_category_profit' => $order->admin_category_profit,
            'seller_profit' => $order->seller_profit,
            'total_profit' => $order->getTotalProfit()
        ]);
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
