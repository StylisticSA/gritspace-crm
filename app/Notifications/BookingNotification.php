<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BookingNotification extends Notification implements ShouldQueue
{
    use Queueable;
    
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

    public function toMail(object $notifiable): MailMessage
    {
        $type     = ucfirst($this->booking['room_type'] ?? 'Office');
        $room     = $this->booking['room_type'] ?? 'Office';
        $user     = $this->booking['user_name'] ?? 'Guest';
        $location = $this->booking['location'] ?? 'Grit Space';
        $category = $this->booking['category'] ?? '';

        // Resolve booking URL
        if ($category === 'closed-office' || $category === 'closed-offices') {
            $url = url("/view-closed/{$this->booking['id']}");
        } elseif ($category === 'dedicated-desk' || $category === 'dedicated-desks') {
            $url = url("/view-dedicated/{$this->booking['id']}");
        } else {
            Log::warning("Unknown booking category: {$category}");
            return (new MailMessage())
                ->subject('Booking Notification')
                ->line("Unable to generate booking link due to unknown category.");
        }

        Log::info("Resolved booking URL: {$url}");

        // CREATED: Admin
        if ($this->eventType === 'created' && in_array($this->recipientType, ['admin', 'super_admin'])) {
            return (new MailMessage())
                ->subject("New Booking Enquiry")
                ->greeting("Hello Team!")
                ->line("Congratulations! You received a new {$location} Booking Enquiry from {$user} for {$room}.")
                ->line("Please follow the link below to view the booking request and mark it as: Approve / Reject / Cancel.")
                ->action('View Booking', $url)
                ->line("Keep up the good work!");

        }

        // CREATED: User
        if ($this->eventType === 'created' && $this->recipientType === 'user') {
            return (new MailMessage())
                ->subject("Grit Space Enquiry")
                ->greeting("Thank you!")
                ->line("Dear {$user}, we have successfully received your enquiry for {$room} at $location}.")
                ->line("We will get back to you shortly to confirm availability and send instructions to complete your booking.")
                ->line("Please follow the link below to view your pending booking request.")
                ->action('View Booking', $url)
                ->line("We hope to welcome you soon!");

        }

        // Paid: Admin
        if ($this->eventType === 'paid' && in_array($this->recipientType, ['admin', 'super_admin'])) {
            return (new MailMessage())
                ->subject("paid {$room} Enquiry")
                ->greeting("Enquiry paid!")
                ->line("The {$location} Booking Enquiry from {$user} for {$room} was marked as Paid.")
                ->line("Please follow the link below to view the Paid booking.")
                ->action('View Booking', $url);
               

        }

        // Paid: User
        if ($this->eventType === 'paid' && $this->recipientType === 'user') {
            return (new MailMessage())
                ->subject("Your Grit Space is available")
                ->greeting("You’re in luck!")
                ->line("Dear {$user}, {$room} at {$location} has been marked Paid.")
                ->line("Please follow the link below to view the Paid booking.")
                ->action('View Booking', $url);
                

        }

        // APPROVED: Admin
        if ($this->eventType === 'approved' && in_array($this->recipientType, ['admin', 'super_admin'])) {
            return (new MailMessage())
                ->subject("Approved {$room} Enquiry")
                ->greeting("Enquiry Approved!")
                ->line("The {$location} Booking Enquiry from {$user} for {$room} was marked as approved.")
                ->line("Please follow the link below to view the approved booking.")
                ->action('View Booking', $url)
                ->line("Let’s seal the deal!");

        }

        // APPROVED: User
        if ($this->eventType === 'approved' && $this->recipientType === 'user') {
            return (new MailMessage())
                ->subject("Your Grit Space is available")
                ->greeting("You’re in luck!")
                ->line("Dear {$user}, {$room} at {$location} is available on your selected dates.")
                ->line("Please follow the link below to view your approved enquiry and proceed to secure checkout to complete your booking.")
                ->action('View Booking', $url)
                ->line("Let’s seal the deal!");

        }

        // REJECTED: Admin
        if ($this->eventType === 'rejected' && in_array($this->recipientType, ['admin', 'super_admin'])) {
            return (new MailMessage())
                ->subject("Rejected {$room} Enquiry")
                ->greeting("Enquiry Rejected")
                ->line("The {$location} Booking Enquiry from {$user} for {$room} was marked as rejected. The user was informed that the Space is unfortunately unavailable on the selected dates.")
                ->line("Please follow the link below to view the rejected booking.")
                ->action('View Booking', $url)
                ->line("Rather safe than sorry!");

        }

        // REJECTED: User
        if ($this->eventType === 'rejected' && $this->recipientType === 'user') {
            return (new MailMessage())
                ->subject("The Grit Space is unavailable")
                ->greeting("Oh no!")
                ->line("Dear {$user}, we’re sorry to inform you that {$room} at {$location} is unfortunately not available on your selected dates.")
                ->line("You are welcome to contact us directly if you have any questions.")
                ->line("Better luck next time!");

        }

        // CANCELLED: Admin
        if ($this->eventType === 'cancelled' && in_array($this->recipientType, ['admin', 'super_admin'])) {
            return (new MailMessage())
                ->subject("Booking Cancelled")
                ->greeting("Booking Cancelled")
                ->line("The {$location} Booking Enquiry from[{$user} for {$room} was marked as cancelled. The user was also notified about the cancellation.")
                ->line("Please follow the link below to view the cancelled booking.")
                ->action('View Booking', $url)
                ->line("On to the next one!");

        }

        // CANCELLED: User
        if ($this->eventType === 'cancelled' && $this->recipientType === 'user') {
            return (new MailMessage())
                ->subject("Booking Cancelled")
                ->greeting("Booking Cancelled")
                ->line("Dear {$user}, your {$room} at {$location} booking has now been cancelled.")
                ->line("Please contact us directly if you have any questions.")
                ->line("Let’s catch up soon!");

        }

        // Unhandled case
        Log::warning("Unhandled eventType or recipientType: {$this->eventType} / {$this->recipientType}");
        return (new MailMessage())
            ->subject('Booking Notification')
            ->line("No email content available for this booking event.");
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $type = ucfirst($this->booking['room_type'] ?? 'Office');

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
            'title' => ucfirst($this->eventType) . ' Booking',
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
