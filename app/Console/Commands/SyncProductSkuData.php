<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\AliExpressService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncProductSkuData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync-sku-data {product_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync SKU data from AliExpress for products that need it';

    protected $aliexpressService;

    /**
     * Create a new command instance.
     */
    public function __construct(AliExpressService $aliexpressService)
    {
        parent::__construct();
        $this->aliexpressService = $aliexpressService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productId = $this->argument('product_id');

        if ($productId) {
            // Sync specific product
            $product = Product::find($productId);
            if (!$product) {
                $this->error("Product with ID {$productId} not found");
                return 1;
            }

            $this->syncProduct($product);
        } else {
            // Sync all AliExpress products that don't have SKU data
            $products = Product::whereNotNull('aliexpress_id')
                ->where(function($query) {
                    $query->whereNull('aliexpress_data')
                        ->orWhereNull('aliexpress_variants');
                })
                ->get();

            $this->info("Found {$products->count()} products to sync");

            $bar = $this->output->createProgressBar($products->count());
            $bar->start();

            foreach ($products as $product) {
                $this->syncProduct($product);
                $bar->advance();

                // Rate limiting - sleep for 1 second between requests
                sleep(1);
            }

            $bar->finish();
            $this->newLine();
        }

        $this->info('SKU data sync completed!');
        return 0;
    }

    /**
     * Sync SKU data for a single product
     */
    protected function syncProduct(Product $product)
    {
        try {
            $this->line("\nSyncing product: {$product->name} (ID: {$product->id})");

            if (empty($product->aliexpress_id)) {
                $this->warn("  ⚠ Skipped - No AliExpress ID");
                return;
            }

            // Fetch SKU data from AliExpress
            $skuData = $this->aliexpressService->fetchProductSkuData($product->aliexpress_id);

            if (!empty($skuData['full_data'])) {
                // Update product with full data
                $product->update([
                    'aliexpress_data' => $skuData['full_data'],
                    'aliexpress_variants' => $skuData['sku_data'],
                    'last_synced_at' => now()
                ]);

                // Count SKUs
                $skuCount = 0;
                if (isset($skuData['full_data']['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o'])) {
                    $skuCount = count($skuData['full_data']['ae_item_sku_info_dtos']['ae_item_sku_info_d_t_o']);
                }

                $this->info("  ✓ Success - Found {$skuCount} SKUs");

                // Show first SKU for reference
                $firstSku = $this->aliexpressService->getFirstAvailableSku($skuData['full_data']);
                if ($firstSku) {
                    $this->line("  → First SKU: {$firstSku}");
                }
            } else {
                $this->warn("  ⚠ No SKU data returned");
            }

        } catch (\Exception $e) {
            $this->error("  ✗ Failed: " . $e->getMessage());
            Log::error('SKU sync failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
