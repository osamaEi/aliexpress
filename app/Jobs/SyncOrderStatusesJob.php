<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\AliExpressService;
use App\Events\OrderStatusUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncOrderStatusesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * Syncs order statuses from AliExpress for all active orders
     */
    public function handle(AliExpressService $aliexpressService): void
    {
        Log::info('Starting automatic order status sync');

        // Get all orders that need status sync
        // Only sync orders that are placed, paid, or shipped (not delivered, cancelled, or failed)
        $orders = Order::whereIn('status', ['placed', 'paid', 'shipped'])
            ->whereNotNull('aliexpress_order_id')
            ->orderBy('updated_at', 'asc')
            ->limit(50) // Process 50 orders at a time to avoid API rate limits
            ->get();

        $successCount = 0;
        $errorCount = 0;
        $statusChangedCount = 0;

        foreach ($orders as $order) {
            try {
                $oldStatus = $order->status;

                // Get order details from AliExpress
                $orderDetails = $aliexpressService->getOrderDetails($order->aliexpress_order_id);

                if ($orderDetails) {
                    // Extract order status
                    $aliexpressStatus = $orderDetails['order_status'] ?? null;

                    if ($aliexpressStatus) {
                        $newStatus = $this->mapAliExpressStatus($aliexpressStatus);

                        if ($newStatus && $newStatus !== $oldStatus) {
                            $order->status = $newStatus;

                            // Update timestamps
                            $this->updateOrderTimestamps($order, $newStatus);

                            $order->save();

                            // Dispatch event
                            event(new OrderStatusUpdated($order, $oldStatus, $newStatus));

                            $statusChangedCount++;

                            Log::info('Order status updated via sync job', [
                                'order_id' => $order->id,
                                'aliexpress_order_id' => $order->aliexpress_order_id,
                                'old_status' => $oldStatus,
                                'new_status' => $newStatus,
                            ]);
                        }
                    }
                }

                // Also try to sync tracking info
                $trackingData = $aliexpressService->getOrderShippingInfo($order->aliexpress_order_id);

                if ($trackingData) {
                    $order->tracking_number = $trackingData['tracking_number'] ?? $order->tracking_number;
                    $order->shipping_method = $trackingData['shipping_method'] ?? $order->shipping_method;

                    // Update status based on shipping status if needed
                    if (isset($trackingData['status'])) {
                        $shippingStatus = $trackingData['status'];
                        $statusChanged = false;

                        if ($shippingStatus === 'delivered' && $order->status !== 'delivered') {
                            $order->status = 'delivered';
                            if (empty($order->delivered_at)) {
                                $order->delivered_at = now();
                            }
                            if (empty($order->shipped_at)) {
                                $order->shipped_at = now()->subDays(3);
                            }
                            $statusChanged = true;
                        } elseif (in_array($shippingStatus, ['in_transit', 'shipped']) && in_array($order->status, ['placed', 'paid'])) {
                            $order->status = 'shipped';
                            if (empty($order->shipped_at)) {
                                $order->shipped_at = now();
                            }
                            $statusChanged = true;
                        }

                        if ($statusChanged && $oldStatus !== $order->status) {
                            event(new OrderStatusUpdated($order, $oldStatus, $order->status));
                            $statusChangedCount++;
                        }
                    }

                    $order->save();

                    // Update Shipping record
                    \App\Models\Shipping::updateOrCreate(
                        ['order_id' => $order->id],
                        [
                            'tracking_number' => $trackingData['tracking_number'] ?? null,
                            'carrier_name' => $trackingData['carrier_name'] ?? null,
                            'carrier_code' => $trackingData['carrier_code'] ?? null,
                            'status' => $trackingData['status'] ?? 'pending',
                            'tracking_events' => $trackingData['tracking_events'] ?? [],
                            'shipped_at' => $order->shipped_at,
                            'delivered_at' => $order->delivered_at,
                            'last_synced_at' => now(),
                        ]
                    );
                }

                $successCount++;

                // Small delay to avoid API rate limits
                usleep(200000); // 200ms delay between requests

            } catch (\Exception $e) {
                $errorCount++;
                Log::error('Failed to sync order status in job', [
                    'order_id' => $order->id,
                    'aliexpress_order_id' => $order->aliexpress_order_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Completed automatic order status sync', [
            'total_orders' => $orders->count(),
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'status_changed_count' => $statusChangedCount,
        ]);
    }

    /**
     * Map AliExpress order status to internal status
     */
    protected function mapAliExpressStatus(string $aliexpressStatus): ?string
    {
        $statusMap = [
            'PLACE_ORDER_SUCCESS' => 'placed',
            'IN_CANCEL' => 'processing',
            'WAIT_SELLER_SEND_GOODS' => 'paid',
            'SELLER_PART_SEND_GOODS' => 'paid',
            'WAIT_BUYER_ACCEPT_GOODS' => 'shipped',
            'FUND_PROCESSING' => 'delivered',
            'FINISH' => 'delivered',
            'ORDER_PLACED' => 'placed',
            'ORDER_CONFIRMED' => 'paid',
            'PAYMENT_CONFIRMED' => 'paid',
            'ORDER_SHIPPED' => 'shipped',
            'ORDER_DELIVERED' => 'delivered',
            'SHIPPED' => 'shipped',
            'DELIVERED' => 'delivered',
        ];

        return $statusMap[strtoupper($aliexpressStatus)] ?? null;
    }

    /**
     * Update order timestamps based on status
     */
    protected function updateOrderTimestamps(Order $order, string $status): void
    {
        switch ($status) {
            case 'placed':
                if (empty($order->placed_at)) {
                    $order->placed_at = now();
                }
                break;

            case 'shipped':
                if (empty($order->shipped_at)) {
                    $order->shipped_at = now();
                }
                break;

            case 'delivered':
                if (empty($order->delivered_at)) {
                    $order->delivered_at = now();
                }
                // Ensure shipped_at is set
                if (empty($order->shipped_at)) {
                    $order->shipped_at = now()->subDays(3);
                }
                break;
        }
    }
}
