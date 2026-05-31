<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;

class LoginCode extends Notification
{
    use Queueable;

    public function __construct(public string $code) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return $notifiable->email ? ['mail'] : ['vonage'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your login code')
            ->view('mail.login-code', ['code' => $this->code]);
    }

    public function toVonage(object $notifiable): VonageMessage
    {
        return (new VonageMessage)
            ->content("Your verification code is {$this->code}");
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
