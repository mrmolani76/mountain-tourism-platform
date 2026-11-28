<?php

namespace Modules\Saas\App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OwnerCredentialsNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $tenantName,
        private string $tenantDomain,
        private string $email,
        private string $temporaryPassword
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Mountain Tourism Owner account is ready')
            ->greeting('Welcome to '.$this->tenantName)
            ->line('Your Owner account is ready.')
            ->line('Tenant address: '.$this->tenantDomain)
            ->line('Login email: '.$this->email)
            ->line('Temporary password: '.$this->temporaryPassword)
            ->line('You must change this password the first time you sign in.');
    }
}
