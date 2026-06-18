<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestOrderProfit extends Command
{
    /**
     * Usage examples:
     *   php artisan order:test-profit 149
     *   php artisan order:test-profit 149 --seller=5 --qty=2 --freight=28.93
     *   php artisan order:test-profit 149 --dry   (calculate only, do not save)
     */
    protected $signature = 'order:test-profit
        {product : The product ID to order}
        {--seller= : Seller user ID (defaults to a seller the product is assigned to)}
        {--qty=1 : Quantity}
        {--freight= : Mock shipping cost in AED (default 28.93)}
        {--dry : Only calculate / preview, do not create the order or touch the wallet}';

    protected $description = 'Mock shipping + create an order for a product and print the profit breakdown';

    public function handle(): int
    {
        $product = Product::find($this->argument('product'));
        if (!$product) {
            $this->error("Product #{$this->argument('product')} not found.");
            return self::FAILURE;
        }

        $quantity     = max(1, (int) $this->option('qty'));
        $mockFreight  = $this->option('freight') !== null ? (float) $this->option('freight') : 28.93;

        // --- Resolve the seller ---
        $seller = $this->resolveSeller($product);
        if (!$seller) {
            $this->error('No seller found. Pass --seller=<id> or assign the product to a seller first.');
            return self::FAILURE;
        }

        // --- Pricing: mirror OrderController::store exactly ---
        $pivot = DB::table('product_user')
            ->where('user_id', $seller->id)
            ->where('product_id', $product->id)
            ->first();

        $unitPrice = ($pivot && ($pivot->price ?? 0) > 0)
            ? (float) $pivot->price
            : (float) $product->price;

        $productTotal = $unitPrice * $quantity;
        $totalPrice   = $productTotal + $mockFreight;

        // --- Header ---
        $this->newLine();
        $this->info("=== Order Test for product #{$product->id} — {$product->name} ===");
        $this->line("Seller:            {$seller->name} (#{$seller->id})");
        $this->line('Product->price (base):    ' . number_format((float) $product->price, 2) . ' AED');
        $this->line('Pivot price (final/sell): ' . ($pivot ? number_format((float) ($pivot->price ?? 0), 2) : 'not assigned') . ' AED');
        $this->newLine();

        $this->info('--- Order Summary (mock) ---');
        $this->table(['Field', 'Value (AED)'], [
            ['Unit price (used)', number_format($unitPrice, 2)],
            ['Quantity', $quantity],
            ['Product total', number_format($productTotal, 2)],
            ['Shipping (mock)', number_format($mockFreight, 2)],
            ['TOTAL (deducted)', number_format($totalPrice, 2)],
        ]);

        // --- Wallet check ---
        $wallet = $seller->wallet;
        if (!$wallet) {
            $this->warn('Seller has no wallet. Profit will still be calculated, but no debit will happen.');
        } else {
            $this->line('Wallet balance:    ' . number_format((float) $wallet->balance, 2) . ' AED');
            if ((float) $wallet->balance < $totalPrice && !$this->option('dry')) {
                $this->error('Insufficient wallet balance — order would be rejected in real flow.');
                return self::FAILURE;
            }
        }
        $this->newLine();

        // --- Build the order (do not persist yet so we can show calc in dry mode) ---
        $order = new Order([
            'user_id'         => $seller->id,
            'order_number'    => Order::generateOrderNumber(),
            'product_id'      => $product->id,
            'quantity'        => $quantity,
            'unit_price'      => $unitPrice,
            'total_price'     => $totalPrice,
            'freight_amount'  => $mockFreight,
            'total_amount'    => $totalPrice,
            'currency'        => $product->currency ?? 'AED',
            'customer_name'   => 'CLI Test Customer',
            'customer_phone'  => '0500000000',
            'phone_country'   => 'AE',
            'shipping_address'  => 'Mock address',
            'shipping_city'     => 'Dubai',
            'shipping_province' => 'Dubai',
            'shipping_country'  => 'AE',
            'shipping_zip'      => '00000',
            'status'          => 'pending',
            'payment_status'  => 'paid',
        ]);

        if ($this->option('dry')) {
            // Run the same calculation the observer would, without saving.
            $order->setRelation('product', $product);
            $order->calculateProfits();
            $this->printBreakdown($order, $totalPrice);
            $this->warn('DRY RUN — nothing was saved, wallet untouched.');
            return self::SUCCESS;
        }

        // --- Persist: OrderObserver::creating() runs calculateProfits() automatically ---
        DB::beginTransaction();
        try {
            $order->save();
            if ($wallet) {
                $wallet->debit($totalPrice, 'order_payment', 'CLI test order #' . $order->order_number);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $order->refresh();
        $this->info("Order created: {$order->order_number} (#{$order->id})");
        if ($wallet) {
            $this->line('Wallet balance after: ' . number_format((float) $wallet->fresh()->balance, 2) . ' AED');
        }
        $this->newLine();

        $this->printBreakdown($order, $totalPrice);

        $this->newLine();
        $this->comment("To remove this test order:  php artisan tinker --execute=\"App\\Models\\Order::find({$order->id})->forceDelete();\"");

        return self::SUCCESS;
    }

    private function resolveSeller(Product $product): ?User
    {
        if ($this->option('seller')) {
            return User::find($this->option('seller'));
        }

        $assignedId = DB::table('product_user')
            ->where('product_id', $product->id)
            ->value('user_id');

        if ($assignedId) {
            return User::find($assignedId);
        }

        // Last resort: any seller account
        return User::where('user_type', 'seller')->first();
    }

    private function printBreakdown(Order $order, float $totalPaid): void
    {
        $aliexpress = (float) $order->aliexpress_profit;
        $seller     = (float) $order->seller_profit;
        $totalProfit = $aliexpress + $seller;

        $this->info('--- Profit Breakdown (calculateProfits) ---');
        $this->table(['Profit source', 'Amount (AED)'], [
            ['AliExpress (supplier margin)', number_format($aliexpress, 2)],
            ['Seller share', number_format($seller, 2)],
            ['── TOTAL PROFIT', number_format($totalProfit, 2)],
        ]);

        $this->line('Total paid by seller (product + shipping): ' . number_format($totalPaid, 2) . ' AED');
        $this->line('Note: admin_category_profit was removed; admin profit lives inside product price only.');
    }
}
