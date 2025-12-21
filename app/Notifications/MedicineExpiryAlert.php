<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MedicineExpiryAlert extends Notification
{
    use Queueable;

    protected $medicine;
    protected $stock;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($medicine, $stock, $type)
    {
        $this->medicine = $medicine;
        $this->stock = $stock;
        $this->type = $type;
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
        return (new MailMessage)
                    ->line("Medicine '{$this->medicine->name}' is {$this->type} with a quantity of {$this->stock->quantity}.")
                    ->line("Expiry Date: {$this->stock->expiry_date}")
                    ->action('View Medicine', url('/medicines/' . $this->medicine->id))
                    ->line('Please take action accordingly.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'medicine_id' => $this->medicine->id,
            'stock_id' => $this->stock->id,
            'type' => $this->type,
            'expiry_date' => $this->stock->expiry_date,
            'quantity' => $this->stock->quantity,
        ];
    }
}
