<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\AliExpressService;

class TestOrderSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:test-sync {order_id : The order ID to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test order status sync for a specific order';

    /**
     * Execute the console command.
     */
    public function handle(AliExpressService $aliexpressService)
    {
        $orderId = $this->argument('order_id');

        $order = Order::find($orderId);

        if (!$order) {
            $this->error("Order #{$orderId} not found!");
            return 1;
        }

        $this->info("Testing order sync for Order #{$order->id}");
        $this->info("Current status: {$order->status}");
        $this->info("AliExpress Order ID: {$order->aliexpress_order_id}");
        $this->line('');

        // Display current timestamps
        $this->table(
            ['Field', 'Value'],
            [
                ['Status', $order->status],
                ['Placed At', $order->placed_at ?? 'Not set'],
                ['Shipped At', $order->shipped_at ?? 'Not set'],
                ['Delivered At', $order->delivered_at ?? 'Not set'],
                ['Tracking Number', $order->tracking_number ?? 'Not set'],
                ['Shipping Method', $order->shipping_method ?? 'Not set'],
            ]
        );

        $this->line('');

        if (!$order->aliexpress_order_id) {
            $this->error('This order has not been placed on AliExpress yet!');
            return 1;
        }

        $this->info('Fetching order details from AliExpress...');

        try {
            // Get order details
            $orderDetails = $aliexpressService->getOrderDetails($order->aliexpress_order_id);

            if ($orderDetails) {
                $this->info('Order details received from AliExpress:');
                $this->line(json_encode($orderDetails, JSON_PRETTY_PRINT));
            } else {
                $this->warn('No order details returned from AliExpress');
            }

            $this->line('');
            $this->info('Fetching tracking information...');

            // Get tracking info
            $trackingData = $aliexpressService->getOrderShippingInfo($order->aliexpress_order_id);

            if ($trackingData) {
                $this->info('Tracking information received:');
                $this->line(json_encode($trackingData, JSON_PRETTY_PRINT));
            } else {
                $this->warn('No tracking information available yet');
            }

            $this->line('');
            $this->info('You can now manually update the order status in the admin panel or run the sync job.');
            $this->info('To run the sync job manually: php artisan queue:work --once');

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
