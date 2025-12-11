<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ClientStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;
    
    protected array $booking;

    public function __construct(array $booking, string $status, string $recipientType)
    {
        if (!in_array($status, ['approved', 'deactivated'])) {
            throw new \InvalidArgumentException("Unsupported status: {$status}");
        }

        $this->booking = array_merge($booking, [
            'status' => $status,
            'recipient_type' => strtolower($recipientType),
        ]);
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status    = $this->booking['status'];
        $user      = $this->booking['user_name'];
        $userEmail = $this->booking['user_email'];
        $recipient = strtolower($this->booking['recipient_type'] ?? 'user');

        $userURL  = url('/company-details');
        $adminURL = url('/admin/client-information');

        if ($status === 'approved') {
            if ($recipient === 'admin') {
                return (new MailMessage())
                    ->subject('Client Approval')
                    ->greeting('Hi Admin\'s')
                     ->line("Client Name: {$user} ")
                    ->line("Client Email: {$userEmail} ")
                    ->line("This client been Approved.")
                    ->action('View Client', $adminURL);
            }

            if ($recipient === 'user') {
                return (new MailMessage())
                    ->subject('Welcome to Grit Space!')
                    ->greeting("Hi {$user}")
                    ->line("We’re thrilled to have you on board!")
                    ->line("Your profile has been approved and you're now part of the Grit Space community.")
                    ->action('View Your Profile', $userURL)
                    ->line("Let’s build something great together!");
            }
        }

        if ($status === 'deactivated') {
            if ($recipient === 'admin') {
                return (new MailMessage())
                    ->subject('Client Deactivated')
                    ->greeting('Hi Admin\'s')
                    ->line("Client Name: {$user} ")
                    ->line("Client Email: {$userEmail} ")
                    ->line("This client been deactivated.")
                    ->action('View Client', $adminURL);
            }

            if ($recipient === 'user') {
                return (new MailMessage())
                    ->subject('Profile Deactivated')
                    ->greeting("Hi {$user}")
                    ->line("We regret to inform you that your profile has been deactivated as a result of recent circumstances.")
                    ->action('View Your Profile', $userURL)
                    ->line("Let’s catch up soon!");
            }
        }

        return (new MailMessage())
            ->subject('Client Status Update')
            ->line('No email content available for this status.');
    }

    public function toDatabase(object $notifiable): array
    {
        $status    = ucfirst($this->booking['status']);
        $recipient = ucfirst($this->booking['recipient_type'] ?? 'User');
        $user      = $this->booking['user_name'];
        $roomType  = $this->booking['room_type'] ?? 'Client';

        return [
            'title'     => "{$status} Client",
            'message'   => "{$roomType} marked as {$status} for {$user} ({$recipient}).",
            'client_id' => $this->booking['id'],
            'status'    => strtolower($this->booking['status']),
            'recipient_type' => strtolower($this->booking['recipient_type'] ?? 'user'),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
