<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Product;
use App\Services\AliExpressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $aliexpressService;

    public function __construct(AliExpressService $aliexpressService)
    {
        $this->aliexpressService = $aliexpressService;
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'product'])->latest();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by order number or customer name
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('aliexpress_order_id', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(20);

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create(Request $request)
    {
        $productId = $request->get('product_id');
        $product = null;

        if ($productId) {
            $product = Product::findOrFail($productId);
        }

        return view('orders.create', compact('product'));
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'selected_sku_attr' => 'nullable|string', // Product variant/SKU
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'phone_country' => 'required|string|max:5',
            'shipping_address' => 'required|string',
            'shipping_address2' => 'nullable|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_province' => 'required|string|max:100', // Required for AliExpress
            'shipping_country' => 'required|string|max:2',
            'shipping_zip' => 'required|string|max:20', // Required for AliExpress
            'customer_notes' => 'nullable|string',
        ]);

        try {
            $product = Product::findOrFail($validated['product_id']);
            $user = auth()->user();

            // Calculate pricing
            $quantity = $validated['quantity'];
            $unitPrice = $product->price;
            $totalPrice = $unitPrice * $quantity;

            // Check seller's wallet balance
            if (!$user->wallet) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Please increase your balance to create orders.');
            }

            if ($user->wallet->balance < $totalPrice) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Insufficient balance. Please increase your balance to create this order. Required: ' . number_format($totalPrice, 2) . ' ' . ($product->currency ?? 'AED') . ', Available: ' . number_format($user->wallet->balance, 2) . ' AED');
            }

            DB::beginTransaction();

            // Create order (profits will be calculated automatically by OrderObserver)
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => Order::generateOrderNumber(),
                'product_id' => $product->id,
                'quantity' => $quantity,
                'selected_sku_attr' => $validated['selected_sku_attr'] ?? null,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'total_amount' => $totalPrice,
                'currency' => $product->currency ?? 'AED',
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'phone_country' => $validated['phone_country'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_address2' => $validated['shipping_address2'],
                'shipping_city' => $validated['shipping_city'],
                'shipping_province' => $validated['shipping_province'],
                'shipping_country' => $validated['shipping_country'],
                'shipping_zip' => $validated['shipping_zip'],
                'customer_notes' => $validated['customer_notes'],
                'status' => 'pending',
                'payment_status' => 'paid', // Mark as paid since we're deducting from wallet
            ]);

            // Deduct amount from seller's wallet
            $user->wallet->debit($totalPrice, 'Order payment for order #' . $order->order_number);

            DB::commit();

            Log::info('Order created and paid from wallet', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $user->id,
                'amount' => $totalPrice,
                'wallet_balance_after' => $user->wallet->fresh()->balance
            ]);

            // Dispatch event to place order on AliExpress
            Log::info('🎯 Dispatching OrderCreated event', [
                'event' => 'OrderCreated',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'product_id' => $order->product_id,
                'is_aliexpress_product' => $order->product->isAliexpressProduct(),
                'payment_status' => $order->payment_status,
                'timestamp' => now()->toDateTimeString()
            ]);

            event(new OrderCreated($order));

            Log::info('✅ OrderCreated event dispatched successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order created successfully! Order Number: ' . $order->order_number . '. Amount deducted from your wallet. Your order will be placed on AliExpress automatically.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order Creation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'product']);

        return view('orders.show', compact('order'));
    }

    /**
     * Place order on AliExpress
     */
    public function placeOnAliexpress(Order $order)
    {
        if (!$order->canBePlaced()) {
            return redirect()->back()
                ->with('error', 'This order cannot be placed on AliExpress.');
        }

        if (!$order->product->isAliexpressProduct()) {
            return redirect()->back()
                ->with('error', 'This product is not from AliExpress.');
        }

        // Check if already placed to prevent duplicates
        if (!empty($order->aliexpress_order_id)) {
            return redirect()->back()
                ->with('error', 'This order has already been placed on AliExpress. Order ID: ' . $order->aliexpress_order_id);
        }

        try {
            // Update status to processing with database lock to prevent race conditions
            $updated = \DB::table('orders')
                ->where('id', $order->id)
                ->where('status', 'pending')
                ->whereNull('aliexpress_order_id')
                ->update(['status' => 'processing', 'updated_at' => now()]);

            // If update failed, order was already processed
            if (!$updated) {
                return redirect()->back()
                    ->with('error', 'This order is already being processed or has been placed.');
            }

            // Refresh the order model
            $order->refresh();

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

            Log::info('Placing order on AliExpress', [
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

                Log::info('Order placed on AliExpress successfully', [
                    'order_id' => $order->id,
                    'aliexpress_order_id' => $result['order_id']
                ]);

                return redirect()->route('orders.show', $order)
                    ->with('success', 'Order placed on AliExpress successfully! AliExpress Order ID: ' . $result['order_id']);
            } else {
                throw new \Exception('Invalid response from AliExpress API');
            }

        } catch (\Exception $e) {
            Log::error('AliExpress Order Placement Error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $order->update([
                'status' => 'failed',
                'admin_notes' => 'Failed to place on AliExpress: ' . $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to place order on AliExpress: ' . $e->getMessage());
        }
    }

    /**
     * Update order tracking information
     */
    public function updateTracking(Order $order)
    {
        if (!$order->aliexpress_order_id) {
            return redirect()->back()
                ->with('error', 'This order has not been placed on AliExpress yet.');
        }

        try {
            $trackingInfo = $this->aliexpressService->getTrackingInfo($order->aliexpress_order_id);

            if ($trackingInfo) {
                $order->update([
                    'tracking_number' => $trackingInfo['tracking_number'] ?? null,
                    'shipping_method' => $trackingInfo['logistics_name'] ?? null,
                    'status' => 'shipped',
                    'shipped_at' => now(),
                ]);

                return redirect()->back()
                    ->with('success', 'Tracking information updated successfully!');
            }

            return redirect()->back()
                ->with('warning', 'No tracking information available yet.');

        } catch (\Exception $e) {
            Log::error('Tracking Update Error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update tracking information.');
        }
    }

    /**
     * Cancel an order
     */
    public function cancel(Order $order)
    {
        if (!$order->canBeCancelled()) {
            return redirect()->back()
                ->with('error', 'This order cannot be cancelled.');
        }

        $order->update(['status' => 'cancelled']);

        return redirect()->route('orders.index')
            ->with('success', 'Order cancelled successfully.');
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,placed,paid,shipped,delivered,cancelled,failed',
            'admin_notes' => 'nullable|string',
            'tracking_number' => 'nullable|string|max:100',
        ]);

        $order->update($validated);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', 'Order deleted successfully.');
    }

    /**
     * Get product details for order creation form
     * Returns product info including whether it's from AliExpress
     */
    public function getProductInfo(Request $request, $productId)
    {
        try {
            $product = Product::findOrFail($productId);

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'currency' => $product->currency ?? 'AED',
                    'is_aliexpress' => $product->isAliexpressProduct(),
                    'aliexpress_id' => $product->aliexpress_id,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Product not found'
            ], 404);
        }
    }

    /**
     * Calculate freight for a product and shipping address
     * API endpoint for AJAX requests from order creation form
     */
    public function calculateFreight(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'country' => 'required|string|size:2',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
        ]);

        try {
            $product = Product::findOrFail($validated['product_id']);

            // Check if product is from AliExpress
            if (!$product->isAliexpressProduct()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This product is not from AliExpress'
                ], 400);
            }

            // Prepare freight calculation parameters
            $freightParams = [
                'product_id' => $product->aliexpress_id,
                'product_num' => $validated['quantity'],
                'country' => $validated['country'],
            ];

            // Add optional parameters if provided
            if (!empty($validated['city'])) {
                $freightParams['city'] = $validated['city'];
            }
            if (!empty($validated['province'])) {
                $freightParams['province'] = $validated['province'];
            }
            if (!empty($product->aliexpress_price)) {
                $freightParams['price'] = $product->aliexpress_price;
            }

            // Call AliExpress API to calculate freight
            $freightResult = $this->aliexpressService->calculateFreight($freightParams);

            Log::info('Freight calculation result', [
                'product_id' => $product->id,
                'aliexpress_id' => $product->aliexpress_id,
                'country' => $validated['country'],
                'result' => $freightResult
            ]);

            // Return the result
            if ($freightResult['success']) {
                return response()->json([
                    'success' => true,
                    'freight_amount' => $freightResult['freight_amount'],
                    'freight_currency' => $freightResult['freight_currency'],
                    'estimated_delivery_time' => $freightResult['estimated_delivery_time'] ?? null,
                    'service_name' => $freightResult['service_name'] ?? null,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $freightResult['error_desc'] ?? 'Unable to calculate freight',
                    'error_code' => $freightResult['error_code'] ?? null,
                    'raw_response' => $freightResult['raw_response'] ?? null, // Include raw response for debugging
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Freight calculation API error', [
                'error' => $e->getMessage(),
                'request' => $validated
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to calculate freight: ' . $e->getMessage()
            ], 500);
        }
    }
}
