<?php

namespace App\Services;

use App\Models\Order;
use App\Events\OrderStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AliExpressWebhookService
{
    /**
     * Verify webhook signature from AliExpress
     */
    public function verifySignature(Request $request): bool
    {
        $webhookSecret = config('services.aliexpress.webhook_secret');

        // If webhook secret is not configured, log warning and allow (for testing)
        if (empty($webhookSecret)) {
            Log::warning('AliExpress webhook secret not configured - skipping signature verification');
            return true;
        }

        // Get signature from header
        $signature = $request->header('X-AliExpress-Signature') ?? $request->header('x-aliexpress-signature');

        if (empty($signature)) {
            Log::warning('No signature found in webhook request');
            return false;
        }

        // Generate expected signature
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        // Compare signatures
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process incoming webhook data
     */
    public function processWebhook(array $data): array
    {
        try {
            // Extract order ID - support multiple possible field names
            $aliexpressOrderId = $data['order_id'] ??
                                 $data['orderId'] ??
                                 $data['ae_order_id'] ??
                                 $data['aeOrderId'] ??
                                 null;

            if (empty($aliexpressOrderId)) {
                return [
                    'success' => false,
                    'error' => 'No order ID found in webhook payload',
                ];
            }

            // Find order by AliExpress order ID
            $order = Order::where('aliexpress_order_id', $aliexpressOrderId)->first();

            if (!$order) {
                Log::warning('Order not found for AliExpress order ID', [
                    'aliexpress_order_id' => $aliexpressOrderId,
                ]);

                return [
                    'success' => false,
                    'error' => 'Order not found',
                ];
            }

            // Extract status information
            $orderStatus = $data['order_status'] ??
                          $data['orderStatus'] ??
                          $data['status'] ??
                          null;

            $trackingNumber = $data['tracking_number'] ??
                             $data['trackingNumber'] ??
                             $data['logistics_no'] ??
                             $data['logisticsNo'] ??
                             null;

            $shippingMethod = $data['shipping_method'] ??
                             $data['shippingMethod'] ??
                             $data['logistics_service'] ??
                             $data['logisticsService'] ??
                             null;

            // Store old status for comparison
            $oldStatus = $order->status;

            // Map AliExpress status to our status
            $newStatus = $this->mapAliExpressStatus($orderStatus);

            if (!$newStatus) {
                Log::warning('Unknown AliExpress order status', [
                    'aliexpress_status' => $orderStatus,
                    'order_id' => $order->id,
                ]);

                return [
                    'success' => false,
                    'error' => 'Unknown order status',
                ];
            }

            // Update order
            $order->status = $newStatus;

            // Update tracking information if provided
            if ($trackingNumber) {
                $order->tracking_number = $trackingNumber;
            }

            if ($shippingMethod) {
                $order->shipping_method = $shippingMethod;
            }

            // Update status timestamps
            $this->updateStatusTimestamps($order, $newStatus);

            // Store webhook payload for audit
            $webhookData = $order->aliexpress_response ?? [];
            $webhookData['webhooks'] = $webhookData['webhooks'] ?? [];
            $webhookData['webhooks'][] = [
                'received_at' => now()->toIso8601String(),
                'status' => $orderStatus,
                'data' => $data,
            ];
            $order->aliexpress_response = $webhookData;

            $order->save();

            // Dispatch event for notifications if status changed
            if ($oldStatus !== $newStatus) {
                event(new OrderStatusUpdated($order, $oldStatus, $newStatus));
            }

            return [
                'success' => true,
                'order_id' => $order->id,
                'status' => $newStatus,
                'old_status' => $oldStatus,
            ];

        } catch (\Exception $e) {
            Log::error('Error processing webhook', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process tracking update webhook
     */
    public function processTrackingUpdate(array $data): array
    {
        try {
            $aliexpressOrderId = $data['order_id'] ?? $data['orderId'] ?? null;
            $trackingNumber = $data['tracking_number'] ?? $data['trackingNumber'] ?? null;

            if (empty($aliexpressOrderId)) {
                return [
                    'success' => false,
                    'error' => 'No order ID found',
                ];
            }

            $order = Order::where('aliexpress_order_id', $aliexpressOrderId)->first();

            if (!$order) {
                return [
                    'success' => false,
                    'error' => 'Order not found',
                ];
            }

            if ($trackingNumber) {
                $order->tracking_number = $trackingNumber;
            }

            if (isset($data['shipping_method'])) {
                $order->shipping_method = $data['shipping_method'];
            }

            // Store tracking events if provided
            if (isset($data['tracking_events'])) {
                $webhookData = $order->aliexpress_response ?? [];
                $webhookData['tracking_events'] = $data['tracking_events'];
                $order->aliexpress_response = $webhookData;
            }

            $order->save();

            return [
                'success' => true,
                'order_id' => $order->id,
            ];

        } catch (\Exception $e) {
            Log::error('Error processing tracking update', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Map AliExpress order status to our internal status
     */
    protected function mapAliExpressStatus(?string $aliexpressStatus): ?string
    {
        if (empty($aliexpressStatus)) {
            return null;
        }

        $statusMap = [
            // AliExpress standard statuses
            'PLACE_ORDER_SUCCESS' => 'placed',
            'IN_CANCEL' => 'processing',
            'WAIT_SELLER_SEND_GOODS' => 'paid',
            'SELLER_PART_SEND_GOODS' => 'paid',
            'WAIT_BUYER_ACCEPT_GOODS' => 'shipped',
            'FUND_PROCESSING' => 'delivered',
            'IN_ISSUE' => 'processing',
            'IN_FROZEN' => 'processing',
            'WAIT_SELLER_EXAMINE_MONEY' => 'processing',
            'RISK_CONTROL' => 'processing',
            'FINISH' => 'delivered',

            // Common variations
            'ORDER_PLACED' => 'placed',
            'ORDER_CONFIRMED' => 'paid',
            'PAYMENT_CONFIRMED' => 'paid',
            'ORDER_PENDING_SHIP' => 'paid',
            'ORDER_SHIPPED' => 'shipped',
            'ORDER_DELIVERED' => 'delivered',
            'ORDER_CANCELLED' => 'cancelled',
            'ORDER_CLOSED' => 'cancelled',

            // Simplified statuses
            'PENDING' => 'pending',
            'PROCESSING' => 'processing',
            'PLACED' => 'placed',
            'PAID' => 'paid',
            'SHIPPED' => 'shipped',
            'DELIVERED' => 'delivered',
            'CANCELLED' => 'cancelled',
            'FAILED' => 'failed',
        ];

        // Normalize status to uppercase
        $normalizedStatus = strtoupper($aliexpressStatus);

        return $statusMap[$normalizedStatus] ?? null;
    }

    /**
     * Update status-related timestamps
     */
    protected function updateStatusTimestamps(Order $order, string $newStatus): void
    {
        switch ($newStatus) {
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
                break;
        }
    }
}
