<?php

namespace App\Notifications;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewUserRegistered extends Notification implements ShouldQueue
{
    use Queueable;
    
    public $newUser;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {


        if ($notifiable->id === $this->newUser->id) {
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(60),
                [
                    'id' => $this->newUser->id,
                    'hash' => sha1($this->newUser->email),
                ]
            );

            return (new MailMessage())
                ->subject('Verify Your Email Address')
                ->greeting('Hello ' . $this->newUser->name . ',')
                ->line('Thank you for registering. Please verify your email address to continue.')
                ->action('Verify Email', $verificationUrl);
        }



        return (new MailMessage())
            ->subject('New User Registration')
            ->greeting('Hello Admin,')
            ->line('A new user has just registered. Please click here to process the request.')
            ->line('Name: ' . $this->newUser->name)
            ->line('Email: ' . $this->newUser->email)
            ->action('View User', url('/admin/manage/'));

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
