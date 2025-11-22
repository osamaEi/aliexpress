<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateOrderProfits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:recalculate-profits {--order-id=* : Specific order IDs to recalculate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate profit values for all orders or specific orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting profit recalculation...');

        $orderIds = $this->option('order-id');

        $query = Order::with(['product', 'user']);

        if (!empty($orderIds)) {
            $query->whereIn('id', $orderIds);
            $this->info('Recalculating profits for ' . count($orderIds) . ' specific orders...');
        } else {
            $this->info('Recalculating profits for all orders...');
        }

        $orders = $query->get();
        $totalOrders = $orders->count();

        if ($totalOrders === 0) {
            $this->warn('No orders found to recalculate.');
            return 0;
        }

        $this->info("Found {$totalOrders} orders to process.");

        $progressBar = $this->output->createProgressBar($totalOrders);
        $progressBar->start();

        $successful = 0;
        $failed = 0;

        foreach ($orders as $order) {
            try {
                DB::beginTransaction();

                // Recalculate profits
                $order->calculateProfits();
                $order->save();

                DB::commit();
                $successful++;
            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;

                $this->newLine();
                $this->error("Failed to recalculate order #{$order->order_number}: " . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Profit recalculation completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Successful', $successful],
                ['Failed', $failed],
                ['Total', $totalOrders],
            ]
        );

        return 0;
    }
}
