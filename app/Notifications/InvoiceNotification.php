<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceNotification extends Notification implements ShouldQueue
{
     use Queueable;
    
    protected array $invoice;
    protected string $eventType;
    protected string $recipientType;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $invoice, string $eventType = 'created', string $recipientType = 'user')
    {
        $this->invoice = $invoice;
        $this->eventType = $eventType;
        $this->recipientType = $recipientType;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $type = ucfirst($this->invoice['room_type'] ?? 'Invoice');

        $statusText = match ($this->eventType) {
            'paid' => $this->recipientType === 'admin'
                ? "The invoice for $type by {$this->invoice['user_name']} has been marked as paid."
                : "Your invoice for $type has been paid successfully.",

            'pending' => $this->recipientType === 'admin'
                ? "The invoice for $type by {$this->invoice['user_name']} is pending."
                : "Your invoice for $type is currently pending.",

            'cancelled' => $this->recipientType === 'admin'
                ? "The invoice for $type by {$this->invoice['user_name']} has been marked as cancelled."
                : "Your invoice for $type has been cancelled.",

            'monthly' => $this->recipientType === 'admin'
                ? "The invoice for $type by {$this->invoice['user_name']} has been sent to the user this month."
                : "We have sent to you this month's invoice for $type.",

            default => "Invoice update.",
        };

        $link = $this->recipientType === 'admin'
            ? route('admin.invoices.show', $this->invoice['id'])
            : route('userView.invoice', $this->invoice['id']);

        return (new MailMessage())
            ->subject(ucfirst($this->eventType) . ' Invoice')
            ->line($statusText)
            ->action('View invoice', $link);
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $type = ucfirst($this->invoice['room_type'] ?? 'Invoice');

        $statusText = match ($this->eventType) {
            'paid' => $this->recipientType === 'admin'
                ? "The $type invoice by {$this->invoice['user_name']} was marked as paid."
                : "Your $type invoice has been paid.",

            'pending' => $this->recipientType === 'admin'
                ? "The $type invoice by {$this->invoice['user_name']} is pending."
                : "Your $type invoice is currently pending.",

            'cancelled' => $this->recipientType === 'admin'
                ? "The $type invoice by {$this->invoice['user_name']} was marked as cancelled."
                : "Your $type invoice was cancelled.",
            
            'monthly' => $this->recipientType === 'admin'
                ? "The invoice for $type by {$this->invoice['user_name']} has been sent to the user this month."
                : "We have sent to you this month's invoice for $type.",

            default => "$type invoice update.",
        };

        return [
            'title' => ucfirst($this->eventType) . ' Invoice',
            'message' => $statusText,
            'invoice_id' => $this->invoice['id'],
            'room_type' => $this->invoice['room_type'],
            'status' => $this->invoice['status'],
        ];

    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}





