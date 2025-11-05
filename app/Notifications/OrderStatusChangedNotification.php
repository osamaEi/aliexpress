<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $oldStatus;
    protected $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $oldStatus, string $newStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusMessage = $this->getStatusMessage();
        $actionUrl = route('orders.show', $this->order);

        return (new MailMessage)
            ->subject('Order Status Update: ' . $this->order->order_number)
            ->greeting('Hello ' . ($this->order->customer_name ?? 'Customer') . '!')
            ->line($statusMessage)
            ->line('Order Number: ' . $this->order->order_number)
            ->line('Status: ' . ucfirst($this->newStatus))
            ->when($this->order->tracking_number, function ($mail) {
                return $mail->line('Tracking Number: ' . $this->order->tracking_number);
            })
            ->action('View Order Details', $actionUrl)
            ->line('Thank you for your order!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'tracking_number' => $this->order->tracking_number,
            'message' => $this->getStatusMessage(),
        ];
    }

    /**
     * Get status-specific message
     */
    protected function getStatusMessage(): string
    {
        $messages = [
            'pending' => 'Your order is pending and will be processed soon.',
            'processing' => 'Your order is being processed.',
            'placed' => 'Your order has been successfully placed with AliExpress!',
            'paid' => 'Payment has been confirmed for your order.',
            'shipped' => 'Great news! Your order has been shipped and is on its way.',
            'delivered' => 'Your order has been delivered successfully.',
            'cancelled' => 'Your order has been cancelled.',
            'failed' => 'Unfortunately, there was an issue with your order.',
        ];

        return $messages[$this->newStatus] ?? 'Your order status has been updated.';
    }
}
