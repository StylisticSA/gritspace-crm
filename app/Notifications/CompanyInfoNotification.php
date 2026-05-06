<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyInfoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $name;
    protected $email;
    protected $recipientType;
    /**
     * Create a new notification instance.
     */
    public function __construct($name, $email, $recipientType)
    {
        $this->name = $name;
        $this->email = $email;
        $this->recipientType = $recipientType; 
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
    public function toMail($notifiable)
    {
        if ($this->recipientType === 'admin') {
            return (new MailMessage)
                ->subject('Company Details Submitted - Approval Required')
                ->greeting('Hello Administrator')
                ->line("A user has submitted company details that require your approval.")
                ->line("**Name:** {$this->name}")
                ->line("**Email:** {$this->email}")
                ->action('Review Company Details', url('/admin/client-information'))
                ->line('Please log in to review and approve.');
        }

        return (new MailMessage)
            ->subject('Company Details Submitted')
            ->greeting("Hello {$this->name}")
            ->line("Your company details have been submitted successfully.")
            ->line("**Status:** Pending Administrator Approval")
            ->line('We will notify you once approved.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'recipient_type' => $this->recipientType,
        ];
    }



    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
