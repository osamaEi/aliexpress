<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipping;
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
     * Handles DROPSHIPPER_ORDER_STATUS_UPDATE (message_type: 53)
     */
    public function processWebhook(array $data): array
    {
        try {
            // Log the raw webhook payload
            Log::info('Processing AliExpress webhook', [
                'message_type' => $data['message_type'] ?? null,
                'seller_id' => $data['seller_id'] ?? null,
                'site' => $data['site'] ?? null,
            ]);

            // Extract order ID from the new format
            // New format: { "data": { "orderId": 123, "orderStatus": "..." }, "message_type": 53 }
            $webhookData = $data['data'] ?? $data;

            $aliexpressOrderId = $webhookData['orderId'] ??
                                 $webhookData['order_id'] ??
                                 $data['order_id'] ??
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

            // Extract status information from new format
            $orderStatus = $webhookData['orderStatus'] ??
                          $webhookData['order_status'] ??
                          $data['order_status'] ??
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
            $trackingNumber = $data['tracking_number'] ?? $data['trackingNumber'] ?? $data['logistics_no'] ?? null;

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

            // Update order tracking info
            if ($trackingNumber) {
                $order->tracking_number = $trackingNumber;
            }

            if (isset($data['shipping_method'])) {
                $order->shipping_method = $data['shipping_method'];
            }

            // Create or update Shipping record
            $shippingData = [
                'tracking_number' => $trackingNumber,
                'carrier_name' => $data['logistics_name'] ?? $data['service_name'] ?? null,
                'carrier_code' => $data['logistics_service'] ?? null,
                'shipping_method' => $data['shipping_method'] ?? null,
                'status' => $this->mapShippingStatus($data['shipping_status'] ?? $data['status'] ?? 'pending'),
                'origin_country' => $data['origin_country'] ?? null,
                'destination_country' => $data['destination_country'] ?? $order->shipping_country ?? null,
                'raw_response' => $data,
                'last_synced_at' => now(),
            ];

            // Add tracking events if provided
            if (isset($data['tracking_events']) || isset($data['details'])) {
                $shippingData['tracking_events'] = $data['tracking_events'] ?? $data['details'] ?? [];
            }

            // Update timestamps based on status
            if (isset($data['shipped_at']) || isset($data['send_time'])) {
                $shippingData['shipped_at'] = Carbon::parse($data['shipped_at'] ?? $data['send_time']);
            }

            if (isset($data['delivered_at']) || isset($data['receive_time'])) {
                $shippingData['delivered_at'] = Carbon::parse($data['delivered_at'] ?? $data['receive_time']);
            }

            $shipping = Shipping::updateOrCreate(
                ['order_id' => $order->id],
                $shippingData
            );

            // Update order status based on shipping status
            if ($shipping->status === 'delivered' && $order->status !== 'delivered') {
                $order->status = 'delivered';
                $order->delivered_at = $shipping->delivered_at ?? now();
            } elseif ($shipping->status === 'in_transit' && $order->status === 'placed') {
                $order->status = 'shipped';
                $order->shipped_at = $shipping->shipped_at ?? now();
            }

            $order->save();

            Log::info('Shipping tracking updated from webhook', [
                'order_id' => $order->id,
                'shipping_id' => $shipping->id,
                'status' => $shipping->status,
                'tracking_number' => $trackingNumber
            ]);

            return [
                'success' => true,
                'order_id' => $order->id,
                'shipping_id' => $shipping->id,
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
     * Map shipping status from webhook to our status
     */
    protected function mapShippingStatus(string $status): string
    {
        $statusMap = [
            'WAIT_SELLER_SEND_GOODS' => 'pending',
            'SELLER_SEND_GOODS' => 'in_transit',
            'SELLER_PART_SEND_GOODS' => 'in_transit',
            'WAIT_BUYER_ACCEPT_GOODS' => 'in_transit',
            'FINISH' => 'delivered',
            'SHIPPED' => 'in_transit',
            'IN_TRANSIT' => 'in_transit',
            'OUT_FOR_DELIVERY' => 'out_for_delivery',
            'DELIVERED' => 'delivered',
            'EXCEPTION' => 'exception',
            'FAILED' => 'failed',
            'RETURNED' => 'returned',
        ];

        return $statusMap[strtoupper($status)] ?? 'pending';
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

            // New webhook event types (message_type: 53 - DROPSHIPPER_ORDER_STATUS_UPDATE)
            'PAYMENTFAILEDEVENT' => 'failed',
            'ORDERCREATED' => 'placed',
            'ORDERCLOSED' => 'cancelled',
            'PAYMENTAUTHORIZED' => 'paid',
            'ORDERSHIPPED' => 'shipped',
            'ORDERCONFIRMED' => 'paid',
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
