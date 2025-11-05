<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyOrderStatusChange implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderStatusUpdated $event): void
    {
        try {
            $order = $event->order;
            $user = $order->user;

            // Send notification to user
            if ($user) {
                $user->notify(new OrderStatusChangedNotification($order, $event->oldStatus, $event->newStatus));
            }

            // Send notification to customer email if different from user email
            if ($order->customer_email && $order->customer_email !== $user?->email) {
                Notification::route('mail', $order->customer_email)
                    ->notify(new OrderStatusChangedNotification($order, $event->oldStatus, $event->newStatus));
            }

            Log::info('Order status change notification sent', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
                'user_id' => $user?->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send order status notification', [
                'error' => $e->getMessage(),
                'order_id' => $event->order->id,
            ]);
        }
    }
}
