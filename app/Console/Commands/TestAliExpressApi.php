<?php

namespace App\Console\Commands;

use App\Services\AliExpressService;
use Illuminate\Console\Command;

class TestAliExpressApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aliexpress:test {product_id? : AliExpress Product ID to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test AliExpress API connection and credentials';

    /**
     * Execute the console command.
     */
    public function handle(AliExpressService $aliexpressService)
    {
        $this->info('Testing AliExpress API Connection...');
        $this->newLine();

        // Display current configuration
        $this->info('Configuration:');
        $this->line('API Key: ' . (config('services.aliexpress.api_key') ?: 'NOT SET'));
        $this->line('API Secret: ' . (config('services.aliexpress.api_secret') ? '***SET***' : 'NOT SET'));
        $this->line('Tracking ID: ' . (config('services.aliexpress.tracking_id') ?: 'NOT SET'));
        $this->line('Access Token: ' . (config('services.aliexpress.access_token') ?: 'NOT SET'));
        $this->line('API URL: ' . config('services.aliexpress.api_url'));
        $this->newLine();

        // Test with a known product ID
        $productId = $this->argument('product_id') ?: '1005006340579394';

        $this->info("Testing product fetch: Product ID {$productId}");
        $this->newLine();

        try {
            $product = $aliexpressService->getProductDetails($productId);

            if ($product) {
                $this->info('✓ Success! Product found:');
                $this->newLine();
                $this->line('Product ID: ' . ($product['product_id'] ?? 'N/A'));
                $this->line('Title: ' . ($product['product_title'] ?? 'N/A'));
                $this->line('Price: $' . ($product['target_sale_price'] ?? $product['sale_price'] ?? 'N/A'));
                $this->line('Image: ' . ($product['product_main_image_url'] ?? 'N/A'));
                $this->newLine();

                $this->info('Full response:');
                $this->line(json_encode($product, JSON_PRETTY_PRINT));
            } else {
                $this->error('✗ Failed! No product data returned.');
                $this->warn('Check storage/logs/laravel.log for detailed error information.');
            }
        } catch (\Exception $e) {
            $this->error('✗ API Error: ' . $e->getMessage());
            $this->warn('Check storage/logs/laravel.log for detailed error information.');
        }

        $this->newLine();
        $this->info('Test complete.');

        return 0;
    }
}
