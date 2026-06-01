<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;

class Invitation extends Notification
{
    use Queueable;

    public function __construct(public string $rsvpUrl = 'https://danceyourassoff.party') {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return $notifiable->email ? ['mail'] : ['vonage'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->ensureMagicLinkToken($notifiable);
        $invitationUrl = "{$this->rsvpUrl}/rsvp?invitationId={$notifiable->magic_link_token}";
        $firstName = $this->getFirstName($notifiable->name);

        return (new MailMessage)
            ->subject("You're invited to DANCE YOUR ASS OFF!")
            ->view('mail.invitation', ['rsvpUrl' => $invitationUrl, 'firstName' => $firstName])
            ->replyTo('tatebosler@gmail.com', 'Tate Bosler')
            ->replyTo('wendylutter@gmail.com', 'Wendy Lutter');
    }

    public function toVonage(object $notifiable): VonageMessage
    {
        $this->ensureMagicLinkToken($notifiable);
        $firstName = $this->getFirstName($notifiable->name);

        return (new VonageMessage)
            ->content("Hi {$firstName}! You've been invited to DYAO - Friday, August 28, 6:30pm. Please RSVP @ https://tinyurl.com/dyaorsvp by 7/13.");
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

    private function ensureMagicLinkToken(object $notifiable): void
    {
        if (! $notifiable->magic_link_token) {
            $notifiable->magic_link_token = hash('sha256', random_bytes(32));
            $notifiable->save();
        }
    }

    private function getFirstName(string $fullName): string
    {
        return explode(' ', trim($fullName))[0];
    }
}
