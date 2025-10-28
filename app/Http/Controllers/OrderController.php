<?php

namespace App\Http\Controllers;

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
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'phone_country' => 'required|string|max:5',
            'shipping_address' => 'required|string',
            'shipping_address2' => 'nullable|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_province' => 'nullable|string|max:100',
            'shipping_country' => 'required|string|max:2',
            'shipping_zip' => 'nullable|string|max:20',
            'customer_notes' => 'nullable|string',
        ]);

        try {
            $product = Product::findOrFail($validated['product_id']);

            // Calculate pricing
            $quantity = $validated['quantity'];
            $unitPrice = $product->price;
            $totalPrice = $unitPrice * $quantity;

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => Order::generateOrderNumber(),
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
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
            ]);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order created successfully! Order Number: ' . $order->order_number);

        } catch (\Exception $e) {
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

        try {
            $order->update(['status' => 'processing']);

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
                        'sku_attr' => '', // SKU attributes if variant selected
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
}
