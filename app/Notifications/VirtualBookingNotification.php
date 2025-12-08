<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VirtualBookingNotification extends Notification
{
    protected array $booking;
    protected string $eventType;
    protected string $recipientType;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $booking, string $eventType = 'created', string $recipientType = 'user')
    {
        $this->booking = $booking;
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
        $type = ucfirst($this->booking['room_type'] ?? 'Virtual Room');

        $statusText = match ($this->eventType) {
            'created' => $this->recipientType === 'admin'
                ? "{$this->booking['user_name']} submitted a new $type booking."
                : "Your $type booking was created.",
            'approved' => $this->recipientType === 'admin'
                ? "The $type booking by {$this->booking['user_name']} was marked as approved."
                : "Your $type booking was approved.",
            'cancelled' => $this->recipientType === 'admin'
                ? "The $type booking by {$this->booking['user_name']} was marked as cancelled."
                : "Your $type booking was cancelled.",
            'rejected' => $this->recipientType === 'admin'
                ? "The $type booking by {$this->booking['user_name']} was marked as rejected."
                : "Your $type booking was rejected.",
            default => "$type booking update.",
        };

        return (new MailMessage())
            ->subject(ucfirst($this->eventType) . ' Virtual Booking')
            ->line($statusText)
            ->action('View Booking', url("/view-virtual/{$this->booking['id']}"));
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $type = ucfirst($this->booking['room_type'] ?? 'Virtual Room');

        $statusText = match ($this->eventType) {
            'created' => $this->recipientType === 'admin'
                ? "{$this->booking['user_name']} submitted a new $type booking."
                : "Your $type booking was created.",
            'approved' => $this->recipientType === 'admin'
                ? "The $type booking by {$this->booking['user_name']} was marked as approved."
                : "Your $type booking was approved.",
            'cancelled' => $this->recipientType === 'admin'
                ? "The $type booking by {$this->booking['user_name']} was marked as cancelled."
                : "Your $type booking was cancelled.",
            'rejected' => $this->recipientType === 'admin'
                ? "The $type booking by {$this->booking['user_name']} was marked as rejected."
                : "Your $type booking was rejected.",
            default => "$type booking update.",
        };

        return [
            'title' => ucfirst($this->eventType) . ' Virtual Booking',
            'message' => $statusText,
            'booking_id' => $this->booking['id'],
            'room_type' => $this->booking['room_type'],
            'status' => $this->booking['status'],
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
