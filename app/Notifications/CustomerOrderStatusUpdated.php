<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerOrderStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public int $orderId,
        public string $status,
        public string $fulfillmentMethod,
        public string $processorName,
        public array $channels = ['mail', 'database']
    ) {
    }

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order #'.$this->orderId.' is '.$this->label())
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your order status has been updated.')
            ->line('Order ID: #'.$this->orderId)
            ->line('Status: '.$this->label())
            ->line('Fulfillment: '.ucfirst($this->fulfillmentMethod))
            ->line('Updated by: '.$this->processorName)
            ->action('View Order History', route('order.history'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'customer_order_status',
            'order_id' => $this->orderId,
            'title' => 'Order #'.$this->orderId.' '.$this->label(),
            'message' => 'Your '.$this->fulfillmentMethod.' order is now '.$this->label().'.',
            'status' => $this->status,
        ];
    }

    private function label(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }
}
