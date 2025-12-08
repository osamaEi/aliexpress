<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SyncOrderStatusesJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic order status syncing
Schedule::job(new SyncOrderStatusesJob())
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->name('sync-order-statuses')
    ->description('Sync order statuses from AliExpress');
