<?php

namespace App\Notifications;

use App\Models\Party;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RsvpReminder extends Notification
{
    use Queueable;

    public function __construct(
        public Party $party,
        public string $rsvpUrl = 'https://danceyourassoff.party',
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->ensureMagicLinkToken($notifiable);
        $rsvpUrl = "{$this->rsvpUrl}/rsvp?invitationId={$notifiable->magic_link_token}";
        $firstName = $this->getFirstName($notifiable->name);

        $members = $this->party->members;
        $nullMembers = $members->filter(fn ($m) => is_null($m->rsvp));
        $maybeMembers = $members->filter(fn ($m) => $m->rsvp === 'maybe');

        $missingNames = $nullMembers->map(fn ($m) => $this->getFirstName($m->name))->values()->all();
        $maybeNames = $maybeMembers->map(fn ($m) => $this->getFirstName($m->name))->values()->all();

        return (new MailMessage)
            ->subject('Reminder: Please RSVP for DANCE YOUR ASS OFF!')
            ->view('mail.rsvp-reminder', [
                'rsvpUrl' => $rsvpUrl,
                'firstName' => $firstName,
                'situation' => self::resolveSituation($this->party),
                'missingNames' => $missingNames,
                'maybeNames' => $maybeNames,
            ])
            ->replyTo('tatebosler@gmail.com', 'Tate Bosler')
            ->replyTo('wendylutter@gmail.com', 'Wendy Lutter');
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

    public static function resolveSituation(Party $party): string
    {
        $members = $party->members;
        $totalCount = $members->count();
        $nullMembers = $members->filter(fn ($m) => is_null($m->rsvp));
        $maybeMembers = $members->filter(fn ($m) => $m->rsvp === 'maybe');

        $allNull = $nullMembers->count() === $totalCount;
        $anyNull = $nullMembers->isNotEmpty();
        $allMaybe = $maybeMembers->count() === $totalCount;
        $anyMaybe = $maybeMembers->isNotEmpty();
        $isMulti = $totalCount > 1;

        return match (true) {
            $allNull && $isMulti => 'no-responses-multi',
            $allNull => 'no-response-single',
            $anyNull && $isMulti => 'missing-response-multi',
            $allMaybe && $isMulti => 'all-maybe-multi',
            ! $isMulti && $anyMaybe => 'maybe-single',
            default => 'responded-maybe-multi',
        };
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
