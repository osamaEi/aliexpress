<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class MarkOrderDelivered extends Command
{
    /**
     * Usage examples:
     *   php artisan order:deliver 44
     *   php artisan order:deliver ORD-20260619-843D6F
     */
    protected $signature = 'order:deliver
        {order : The order ID or order number}';

    protected $description = 'Mark an order as delivered (تم التوصيل) and stamp delivered_at';

    public function handle(): int
    {
        $key = $this->argument('order');

        $order = is_numeric($key)
            ? Order::find($key)
            : Order::where('order_number', $key)->first();

        if (!$order) {
            $this->error("Order [{$key}] not found.");
            return self::FAILURE;
        }

        if ($order->status === 'delivered') {
            $this->warn("Order {$order->order_number} is already delivered (since {$order->delivered_at}).");
            return self::SUCCESS;
        }

        $previousStatus = $order->status;

        $order->status = 'delivered';
        if (!$order->delivered_at) {
            $order->delivered_at = now();
        }
        // Backfill shipped_at so the timeline stays consistent.
        if (!$order->shipped_at) {
            $order->shipped_at = now();
        }
        $order->save();

        $this->info("Order {$order->order_number} marked as delivered (تم التوصيل).");
        $this->line("  Status: {$previousStatus} → delivered");
        $this->line("  Delivered at: {$order->delivered_at}");

        return self::SUCCESS;
    }
}
