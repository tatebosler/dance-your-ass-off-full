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
            ->subject("You're on the list! Here's your verification code.")
            ->view('mail.login-code', ['code' => $this->code])
            ->replyTo('tatebosler@gmail.com', 'Tate Bosler')
            ->replyTo('wendylutter@gmail.com', 'Wendy Lutter');
    }

    public function toVonage(object $notifiable): VonageMessage
    {
        return (new VonageMessage)
            ->from(config('services.vonage.sms_from'))
            ->content("Your verification code for DYAO is {$this->code}");
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
