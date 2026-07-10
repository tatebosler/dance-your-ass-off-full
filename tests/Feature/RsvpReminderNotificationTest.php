<?php

use App\Models\Party;
use App\Models\User;
use App\Notifications\RsvpReminder;

/** @param  array<int, string|null>  $rsvps */
function makePartyWithMembers(array $rsvps): Party
{
    $party = Party::factory()->create();
    foreach ($rsvps as $rsvp) {
        User::factory()->create(['party_id' => $party->id, 'rsvp' => $rsvp, 'email' => fake()->unique()->safeEmail()]);
    }

    return $party->fresh('members');
}

describe('RsvpReminder Notification', function () {
    test('always sends via email only', function () {
        $party = makePartyWithMembers([null]);
        $user = $party->members->first();

        $notification = new RsvpReminder($party);

        expect($notification->via($user))->toBe(['mail']);
    });

    test('generates magic link token if not set', function () {
        $party = makePartyWithMembers([null]);
        $user = $party->members->first();
        $user->magic_link_token = null;
        $user->save();

        (new RsvpReminder($party))->toMail($user);

        $user->refresh();
        expect($user->magic_link_token)->not->toBeNull();
        expect(strlen($user->magic_link_token))->toBe(64);
    });

    test('preserves existing magic link token', function () {
        $party = makePartyWithMembers([null]);
        $user = $party->members->first();
        $user->magic_link_token = 'existing-token-value';
        $user->save();

        (new RsvpReminder($party))->toMail($user);

        $user->refresh();
        expect($user->magic_link_token)->toBe('existing-token-value');
    });

    test('mail includes RSVP URL with token', function () {
        $party = makePartyWithMembers([null]);
        $user = $party->members->first();

        $notification = new RsvpReminder($party, 'https://danceyourassoff.party');
        $mailMessage = $notification->toMail($user);

        $user->refresh();
        $expectedUrl = "https://danceyourassoff.party/rsvp?invitationId={$user->magic_link_token}";
        expect($mailMessage->viewData)->toHaveKey('rsvpUrl', $expectedUrl);
    });

    test('mail subject is the reminder subject', function () {
        $party = makePartyWithMembers([null]);
        $user = $party->members->first();

        $mailMessage = (new RsvpReminder($party))->toMail($user);

        expect($mailMessage->subject)->toBe('Reminder: Please RSVP for DANCE YOUR ASS OFF!');
    });

    describe('situation selection', function () {
        test('selects no-responses-multi when all members have null rsvp in a multi-person party', function () {
            $party = makePartyWithMembers([null, null]);
            $user = $party->members->first();

            $mailMessage = (new RsvpReminder($party))->toMail($user);

            expect($mailMessage->viewData['situation'])->toBe('no-responses-multi');
        });

        test('selects no-response-single when sole member has null rsvp', function () {
            $party = makePartyWithMembers([null]);
            $user = $party->members->first();

            $mailMessage = (new RsvpReminder($party))->toMail($user);

            expect($mailMessage->viewData['situation'])->toBe('no-response-single');
        });

        test('selects missing-response-multi when some members have null rsvp but others have responded', function () {
            $party = makePartyWithMembers([null, 'yes']);
            $user = $party->members->first();

            $mailMessage = (new RsvpReminder($party))->toMail($user);

            expect($mailMessage->viewData['situation'])->toBe('missing-response-multi');
        });

        test('selects missing-response-multi when null and maybe are mixed in a multi-person party', function () {
            $party = makePartyWithMembers([null, 'maybe']);
            $user = $party->members->first();

            $mailMessage = (new RsvpReminder($party))->toMail($user);

            expect($mailMessage->viewData['situation'])->toBe('missing-response-multi');
        });

        test('selects all-maybe-multi when all members said maybe in a multi-person party', function () {
            $party = makePartyWithMembers(['maybe', 'maybe']);
            $user = $party->members->first();

            $mailMessage = (new RsvpReminder($party))->toMail($user);

            expect($mailMessage->viewData['situation'])->toBe('all-maybe-multi');
        });

        test('selects maybe-single when sole member said maybe', function () {
            $party = makePartyWithMembers(['maybe']);
            $user = $party->members->first();

            $mailMessage = (new RsvpReminder($party))->toMail($user);

            expect($mailMessage->viewData['situation'])->toBe('maybe-single');
        });

        test('selects responded-maybe-multi when all responded but at least one said maybe', function () {
            $party = makePartyWithMembers(['yes', 'maybe']);
            $user = $party->members->first();

            $mailMessage = (new RsvpReminder($party))->toMail($user);

            expect($mailMessage->viewData['situation'])->toBe('responded-maybe-multi');
        });

        test('passes missing names for missing-response-multi situation', function () {
            $party = Party::factory()->create();
            $respondedUser = User::factory()->create(['party_id' => $party->id, 'rsvp' => 'yes', 'name' => 'Jane Doe']);
            $missingUser = User::factory()->create(['party_id' => $party->id, 'rsvp' => null, 'name' => 'John Smith']);
            $party->load('members');

            $mailMessage = (new RsvpReminder($party))->toMail($respondedUser);

            expect($mailMessage->viewData['missingNames'])->toBe(['John']);
        });

        test('passes maybe names for responded-maybe-multi situation', function () {
            $party = Party::factory()->create();
            $yesUser = User::factory()->create(['party_id' => $party->id, 'rsvp' => 'yes', 'name' => 'Jane Doe']);
            $maybeUser = User::factory()->create(['party_id' => $party->id, 'rsvp' => 'maybe', 'name' => 'John Smith']);
            $party->load('members');

            $mailMessage = (new RsvpReminder($party))->toMail($yesUser);

            expect($mailMessage->viewData['maybeNames'])->toBe(['John']);
        });
    });
});
