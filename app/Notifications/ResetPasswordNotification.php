<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;
    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
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
        $url = url("/api/v1/auth/reset-password?token={$this->token}&email=" . $notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('🔐 Reset Your Password')
            ->greeting('Hello 👋')
            ->line('We received a request to reset your password. Here’s your reset token:')
            ->line("**{$this->token}**")
            ->line('👉 Use this token in the app to reset your password.')            
            ->line('If you did not request a password reset, you can ignore this email.')            
            ->line('Thank you for using our application!');
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
