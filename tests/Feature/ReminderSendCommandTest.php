<?php

use App\Models\Party;
use App\Models\User;
use App\Notifications\RsvpReminder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

describe('ReminderSendCommand', function () {
    test('sends reminders to all members of parties with pending RSVPs', function () {
        $party = Party::factory()->create();
        $memberWithNull = User::factory()->create(['party_id' => $party->id, 'rsvp' => null]);
        $memberWithMaybe = User::factory()->create(['party_id' => $party->id, 'rsvp' => 'maybe']);

        $this->artisan('reminder:send')
            ->assertExitCode(0)
            ->expectsOutput('Sent reminders to 2 member(s).');

        Notification::assertSentTo($memberWithNull, RsvpReminder::class);
        Notification::assertSentTo($memberWithMaybe, RsvpReminder::class);
    });

    test('does not send reminders to parties where all members have responded yes or no', function () {
        $party = Party::factory()->create();
        $memberYes = User::factory()->create(['party_id' => $party->id, 'rsvp' => 'yes']);
        $memberNo = User::factory()->create(['party_id' => $party->id, 'rsvp' => 'no']);

        $this->artisan('reminder:send')
            ->assertExitCode(0)
            ->expectsOutput('No parties require reminders.');

        Notification::assertNotSentTo($memberYes, RsvpReminder::class);
        Notification::assertNotSentTo($memberNo, RsvpReminder::class);
    });

    test('sends reminders to all members of a party even if only one member is pending', function () {
        $party = Party::factory()->create();
        $memberYes = User::factory()->create(['party_id' => $party->id, 'rsvp' => 'yes']);
        $memberPending = User::factory()->create(['party_id' => $party->id, 'rsvp' => null]);

        $this->artisan('reminder:send')
            ->assertExitCode(0)
            ->expectsOutput('Sent reminders to 2 member(s).');

        Notification::assertSentTo($memberYes, RsvpReminder::class);
        Notification::assertSentTo($memberPending, RsvpReminder::class);
    });

    test('skips members without an email address', function () {
        $party = Party::factory()->create();
        $memberWithEmail = User::factory()->create(['party_id' => $party->id, 'rsvp' => null, 'email' => 'test@example.com']);
        $memberWithoutEmail = User::factory()->create(['party_id' => $party->id, 'rsvp' => null, 'email' => null]);

        $this->artisan('reminder:send')
            ->assertExitCode(0)
            ->expectsOutput('Sent reminders to 1 member(s).')
            ->expectsOutput('Skipped 1 member(s) with no email address.');

        Notification::assertSentTo($memberWithEmail, RsvpReminder::class);
        Notification::assertNotSentTo($memberWithoutEmail, RsvpReminder::class);
    });

    test('returns success when no parties require reminders', function () {
        $this->artisan('reminder:send')
            ->assertExitCode(0)
            ->expectsOutput('No parties require reminders.');
    });

    test('dry-run outputs a table of parties and templates without sending', function () {
        $party1 = Party::factory()->create(['name' => 'The Smiths']);
        User::factory()->create(['party_id' => $party1->id, 'rsvp' => null]);
        User::factory()->create(['party_id' => $party1->id, 'rsvp' => null]);

        $party2 = Party::factory()->create(['name' => 'Jane Doe']);
        User::factory()->create(['party_id' => $party2->id, 'rsvp' => 'maybe']);

        $this->artisan('reminder:send --dry-run')
            ->expectsTable(
                ['Party ID', 'Party Name', 'Template'],
                [
                    [$party1->id, 'The Smiths', 'no-responses-multi'],
                    [$party2->id, 'Jane Doe', 'maybe-single'],
                ]
            )
            ->assertExitCode(0);

        Notification::assertNothingSent();
    });

    test('dry-run shows no parties message when no reminders are needed', function () {
        $this->artisan('reminder:send --dry-run')
            ->assertExitCode(0)
            ->expectsOutput('No parties require reminders.');

        Notification::assertNothingSent();
    });

    test('--party sends reminders only to the specified party', function () {
        $targetParty = Party::factory()->create();
        $targetMember = User::factory()->create(['party_id' => $targetParty->id, 'rsvp' => null]);

        $otherParty = Party::factory()->create();
        $otherMember = User::factory()->create(['party_id' => $otherParty->id, 'rsvp' => null]);

        $this->artisan("reminder:send --party={$targetParty->id}")
            ->assertExitCode(0)
            ->expectsOutput('Sent reminders to 1 member(s).');

        Notification::assertSentTo($targetMember, RsvpReminder::class);
        Notification::assertNotSentTo($otherMember, RsvpReminder::class);
    });

    test('--party warns and skips parties where all members have already responded', function () {
        $respondedParty = Party::factory()->create(['name' => 'The Smiths']);
        User::factory()->create(['party_id' => $respondedParty->id, 'rsvp' => 'yes']);

        $this->artisan("reminder:send --party={$respondedParty->id}")
            ->assertExitCode(0)
            ->expectsOutput("Skipping \"The Smiths\" ({$respondedParty->id}): all members have already responded.")
            ->expectsOutput('No parties require reminders.');

        Notification::assertNothingSent();
    });

    test('--user sends reminders only to the specified user', function () {
        $party = Party::factory()->create();
        $targetMember = User::factory()->create(['party_id' => $party->id, 'rsvp' => null]);
        $otherMember = User::factory()->create(['party_id' => $party->id, 'rsvp' => null]);

        $this->artisan("reminder:send --user={$targetMember->id}")
            ->assertExitCode(0)
            ->expectsOutput('Sent reminders to 1 member(s).');

        Notification::assertSentTo($targetMember, RsvpReminder::class);
        Notification::assertNotSentTo($otherMember, RsvpReminder::class);
    });

    test('--user sends to a user whose party qualifies even if that user has already responded', function () {
        $party = Party::factory()->create();
        $respondedMember = User::factory()->create(['party_id' => $party->id, 'rsvp' => 'yes']);
        User::factory()->create(['party_id' => $party->id, 'rsvp' => null]);

        $this->artisan("reminder:send --user={$respondedMember->id}")
            ->assertExitCode(0)
            ->expectsOutput('Sent reminders to 1 member(s).');

        Notification::assertSentTo($respondedMember, RsvpReminder::class);
    });

    test('--user warns when user is not found or their party does not require a reminder', function () {
        $party = Party::factory()->create();
        $member = User::factory()->create(['party_id' => $party->id, 'rsvp' => 'yes']);

        $this->artisan("reminder:send --user={$member->id}")
            ->assertExitCode(0)
            ->expectsOutput("Skipping user {$member->id}: not found or their party does not require a reminder.")
            ->expectsOutput('No parties require reminders.');

        Notification::assertNothingSent();
    });

    test('--party and --user cannot be used together', function () {
        $this->artisan('reminder:send --party=1 --user=1')
            ->assertExitCode(1)
            ->expectsOutput('The --party and --user options cannot be used together.');

        Notification::assertNothingSent();
    });

    test('--dry-run works with --party targeting', function () {
        $party = Party::factory()->create(['name' => 'The Smiths']);
        User::factory()->create(['party_id' => $party->id, 'rsvp' => null]);

        $this->artisan("reminder:send --party={$party->id} --dry-run")
            ->expectsTable(
                ['Party ID', 'Party Name', 'Template'],
                [[$party->id, 'The Smiths', 'no-response-single']]
            )
            ->assertExitCode(0);

        Notification::assertNothingSent();
    });
});
