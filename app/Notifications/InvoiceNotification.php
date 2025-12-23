<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

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
                ? "The invoice $type belonging to {$this->invoice['user_name']} has been marked as Paid."
                : "Your invoice $type has been successfully marked as Paid.",

            'pending' => $this->recipientType === 'admin'
                ? "The invoice $type belonging to {$this->invoice['user_name']} has been updated to Pending."
                : "Your invoice $type has been updated to Pending.",

            'cancelled' => $this->recipientType === 'admin'
                ? "The invoice $type belonging to {$this->invoice['user_name']} has been marked as Cancelled."
                : "Your invoice $type has been updated to Cancelled.",

            'monthly' => $this->recipientType === 'admin'
                ? "The invoice $type belonging to {$this->invoice['user_name']} has been sent. Please check the attachment."
                : "We have sent you this month's invoice for $type. Please check the attachment.",
                
            default => "Invoice update.",
        };

        $link = $this->recipientType === 'admin'
            ? route('admin.invoices.show', $this->invoice['id'])
            : route('userView.invoice', $this->invoice['id']);
            
     

        $mail = (new MailMessage)
            ->subject(ucfirst($this->eventType).' Invoice')
            ->line($statusText)
            ->action('View invoice', $link);

        
        
        if ($this->eventType === 'monthly' && !empty($this->invoice['pdf_path'])) {
            $absolutePath = Storage::disk('public')->path($this->invoice['pdf_path']);
      

            if (file_exists($absolutePath)) {
                $mail->attach($absolutePath, [
                    'as' => ($this->invoice['room_type'] ?? 'invoice').'.pdf',
                    'mime' => 'application/pdf',
                ]);
            }
        }



        return $mail;


    
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





