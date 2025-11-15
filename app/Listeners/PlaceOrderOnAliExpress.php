<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\AliExpressService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PlaceOrderOnAliExpress implements ShouldQueue
{
    use InteractsWithQueue;

    protected $aliexpressService;

    /**
     * Create the event listener.
     */
    public function __construct(AliExpressService $aliexpressService)
    {
        $this->aliexpressService = $aliexpressService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        Log::info('=== OrderCreated Event Triggered ===', [
            'listener' => 'PlaceOrderOnAliExpress',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'product_id' => $order->product_id,
            'payment_status' => $order->payment_status,
            'timestamp' => now()->toDateTimeString()
        ]);

        // Only process if order is for an AliExpress product and payment is completed
        if (!$order->product->isAliexpressProduct() || $order->payment_status !== 'paid') {
            Log::warning('❌ Skipping AliExpress placement - Validation Failed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'is_aliexpress_product' => $order->product->isAliexpressProduct(),
                'payment_status' => $order->payment_status,
                'reason' => !$order->product->isAliexpressProduct() ? 'Not an AliExpress product' : 'Payment not completed'
            ]);
            return;
        }

        Log::info('✅ Validation passed - Starting AliExpress placement', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'aliexpress_product_id' => $order->product->aliexpress_id
        ]);

        try {
            // Update status to processing
            $order->update(['status' => 'processing']);

            Log::info('📝 Order status updated to processing', [
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);

            // Use the SKU that was selected during order creation
            $skuAttr = $order->selected_sku_attr ?? '';

            Log::info('Using user-selected SKU', [
                'order_id' => $order->id,
                'selected_sku_attr' => $skuAttr
            ]);

            // Only auto-select if no SKU was chosen
            if (empty($skuAttr)) {
                Log::info('No SKU selected by user, auto-selecting', [
                    'order_id' => $order->id
                ]);

                // First, check if we have SKU data stored in aliexpress_data
                if (!empty($order->product->aliexpress_data)) {
                    $skuAttr = $this->aliexpressService->getFirstAvailableSku($order->product->aliexpress_data);

                    Log::info('Using SKU from stored aliexpress_data', [
                        'product_id' => $order->product->id,
                        'sku_attr' => $skuAttr
                    ]);
                }

                // If no SKU found, try to fetch from AliExpress API
                if (empty($skuAttr)) {
                    Log::info('No SKU data found locally, fetching from AliExpress', [
                        'product_id' => $order->product->id,
                        'aliexpress_id' => $order->product->aliexpress_id
                    ]);

                    try {
                        $skuData = $this->aliexpressService->fetchProductSkuData($order->product->aliexpress_id);

                        if (!empty($skuData['full_data'])) {
                            // Store the fetched data for future use
                            $order->product->update([
                                'aliexpress_data' => $skuData['full_data'],
                                'aliexpress_variants' => $skuData['sku_data'],
                                'last_synced_at' => now()
                            ]);

                            // Get the first available SKU
                            $skuAttr = $this->aliexpressService->getFirstAvailableSku($skuData['full_data']);

                            Log::info('Fetched and stored SKU data', [
                                'product_id' => $order->product->id,
                                'sku_attr' => $skuAttr
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to fetch SKU data from AliExpress', [
                            'product_id' => $order->product->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Fallback: check old variants structure
                if (empty($skuAttr) && !empty($order->product->aliexpress_variants)) {
                    $variants = $order->product->aliexpress_variants;
                    if (isset($variants[0]['id'])) {
                        $skuAttr = $variants[0]['id'];
                    }
                }
            }

            // Prepare order data for AliExpress
            $orderData = [
                'contact_person' => $order->customer_name,
                'mobile_no' => $order->customer_phone,
                'phone_country' => $order->phone_country,
                'address' => $order->shipping_address,
                'address2' => $order->shipping_address2 ?? '',
                'city' => $order->shipping_city,
                'country' => $order->shipping_country,
                'province' => $order->shipping_province ?? '',
                'zip' => $order->shipping_zip ?? '',
                'full_name' => $order->customer_name,
                'product_items' => [
                    [
                        'product_id' => $order->product->aliexpress_id,
                        'product_count' => $order->quantity,
                        'sku_attr' => $skuAttr,
                    ]
                ]
            ];

            Log::info('Placing order on AliExpress via event listener', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'aliexpress_product_id' => $order->product->aliexpress_id
            ]);

            // Call AliExpress API
            $result = $this->aliexpressService->createOrder($orderData);

            if ($result && isset($result['order_id'])) {
                $order->update([
                    'status' => 'placed',
                    'aliexpress_order_id' => $result['order_id'],
                    'placed_at' => now(),
                    'aliexpress_response' => $result,
                ]);

                Log::info('Order placed on AliExpress successfully via event listener', [
                    'order_id' => $order->id,
                    'aliexpress_order_id' => $result['order_id']
                ]);
            } else {
                throw new \Exception('Invalid response from AliExpress API');
            }

        } catch (\Exception $e) {
            Log::error('AliExpress Order Placement Error (Event Listener)', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $order->update([
                'status' => 'failed',
                'admin_notes' => 'Failed to place on AliExpress: ' . $e->getMessage()
            ]);

            // Re-throw to mark job as failed for retry
            throw $e;
        }
    }
}
