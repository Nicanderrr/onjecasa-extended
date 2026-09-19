<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OnlineOrderAssignedToBranch extends Notification
{
    use Queueable;

    public function __construct(
        public int $orderId,
        public string $customerName,
        public int $itemCount,
        public float $orderTotal,
        public string $fulfillmentMethod,
        public string $branchName,
        public array $channels = ['database']
    ) {
    }

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $actionUrl = ($notifiable->role ?? null) === 'cashier'
            ? route('cashier.online-orders.index')
            : route('pos.admin.online-orders.index');

        return (new MailMessage)
            ->subject('Online Order Needs Processing (#'.$this->orderId.')')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new website order has been sent to '.$this->branchName.'.')
            ->line('Order ID: #'.$this->orderId)
            ->line('Customer: '.$this->customerName)
            ->line('Items: '.$this->itemCount)
            ->line('Total: GHS '.number_format($this->orderTotal, 2))
            ->line('Fulfillment: '.ucfirst($this->fulfillmentMethod))
            ->action('Open Online Orders', $actionUrl)
            ->line('The first staff member to accept this order will be assigned to process it.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'online_order_branch',
            'order_id' => $this->orderId,
            'title' => 'Online order #'.$this->orderId,
            'message' => $this->customerName.' placed a '.$this->fulfillmentMethod.' order for '.$this->branchName.'.',
            'total' => $this->orderTotal,
            'branch' => $this->branchName,
        ];
    }
}
