<?php

use App\Models\User;
use App\Notifications\Invitation;
use Illuminate\Notifications\Messages\VonageMessage;

describe('Invitation Notification', function () {
    test('prefers email over SMS when email is present', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'phone' => '5551234567',
        ]);

        $notification = new Invitation;
        $channels = $notification->via($user);

        expect($channels)->toBe(['mail']);
    })->group('invitation');

    test('uses SMS when email is not present', function () {
        $user = User::factory()->create([
            'email' => null,
            'phone' => '5551234567',
        ]);

        $notification = new Invitation;
        $channels = $notification->via($user);

        expect($channels)->toBe(['vonage']);
    })->group('invitation');

    test('generates magic link token if not set', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'magic_link_token' => null,
        ]);

        $notification = new Invitation;
        $notification->toMail($user);

        $user->refresh();
        expect($user->magic_link_token)->not->toBeNull();
        expect(strlen($user->magic_link_token))->toBe(64); // SHA-256 is 64 hex characters
    })->group('invitation');

    test('preserves existing magic link token', function () {
        $originalToken = 'existing-token-value';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'magic_link_token' => $originalToken,
        ]);

        $notification = new Invitation;
        $notification->toMail($user);

        $user->refresh();
        expect($user->magic_link_token)->toBe($originalToken);
    })->group('invitation');

    test('mail includes invitation URL with token', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'magic_link_token' => null,
        ]);

        $notification = new Invitation('https://danceyourassoff.party');
        $mailMessage = $notification->toMail($user);

        $user->refresh();
        $expectedUrl = "https://danceyourassoff.party/rsvp?invitationId={$user->magic_link_token}";
        expect($mailMessage->viewData)->toHaveKey('rsvpUrl', $expectedUrl);
    })->group('invitation');

    test('SMS message includes correct first name extraction', function () {
        $user = User::factory()->create([
            'name' => 'John Doe Smith',
            'email' => null,
            'phone' => '5551234567',
        ]);

        $notification = new Invitation;
        $vonageMessage = $notification->toVonage($user);

        expect($vonageMessage)->toBeInstanceOf(VonageMessage::class);
        expect($vonageMessage->content)->toContain('Hi John!');
        expect($vonageMessage->content)->toContain('DANCE YOUR ASS OFF');
        expect($vonageMessage->content)->toContain('Friday, August 28, 6:30pm');
        expect($vonageMessage->content)->toContain('danceyourassoff.party');
        expect($vonageMessage->content)->toContain('7/13');
    })->group('invitation');

    test('SMS does not include token URL', function () {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => null,
            'phone' => '5551234567',
            'magic_link_token' => null,
        ]);

        $notification = new Invitation('https://danceyourassoff.party');
        $vonageMessage = $notification->toVonage($user);

        $user->refresh();
        expect($vonageMessage->content)->not->toContain('/rsvp?invitationId=');
        expect($vonageMessage->content)->toContain('danceyourassoff.party');
    })->group('invitation');

    test('SMS message with single name works correctly', function () {
        $user = User::factory()->create([
            'name' => 'Madonna',
            'email' => null,
            'phone' => '5551234567',
        ]);

        $notification = new Invitation;
        $vonageMessage = $notification->toVonage($user);

        expect($vonageMessage->content)->toContain('Hi Madonna!');
    })->group('invitation');
});
