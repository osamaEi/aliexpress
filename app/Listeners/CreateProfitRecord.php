<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\Profit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CreateProfitRecord implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        try {
            $order = $event->order;

            Log::info('📊 Creating profit record for order', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'product_id' => $order->product_id,
                'quantity' => $order->quantity,
            ]);

            // Create profit record from order
            $profit = Profit::createFromOrder($order);

            Log::info('✅ Profit record created successfully', [
                'profit_id' => $profit->id,
                'order_id' => $order->id,
                'aliexpress_price' => $profit->aliexpress_price,
                'admin_profit' => $profit->admin_profit,
                'seller_profit' => $profit->seller_profit,
                'shipping_price' => $profit->shipping_price,
                'total_cost' => $profit->total_cost,
                'total_profit' => $profit->total_profit,
                'final_price' => $profit->final_price,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Failed to create profit record', [
                'order_id' => $event->order->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to mark job as failed if using queue
            throw $e;
        }
    }
}
