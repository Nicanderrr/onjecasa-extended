<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderPlaced extends Notification
{
    use Queueable;

    public function __construct(
        public int $orderId,
        public string $customerName,
        public int $itemCount,
        public float $orderTotal,
        public string $paymentMethod,
        public array $channels = ['database']
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Order Received (#' . $this->orderId . ')')
            ->greeting('Hello Admin,')
            ->line('A new order has been placed on your website.')
            ->line('Order ID: #' . $this->orderId)
            ->line('Customer: ' . $this->customerName)
            ->line('Items: ' . $this->itemCount)
            ->line('Total: GHs ' . number_format($this->orderTotal, 2))
            ->line('Payment Method: ' . ucfirst($this->paymentMethod))
            ->action('View Orders', route('admin_orders'))
            ->line('Please review and process this order.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_order',
            'order_id' => $this->orderId,
            'title' => 'New order #' . $this->orderId,
            'message' => $this->customerName . ' placed an order (' . $this->itemCount . ' item(s)).',
            'total' => $this->orderTotal,
            'payment_method' => $this->paymentMethod,
        ];
    }
}
