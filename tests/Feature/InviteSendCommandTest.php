<?php

use App\Models\User;
use App\Notifications\Invitation;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

describe('InviteSendCommand', function () {
    test('sends invitations to all users with --all flag', function () {
        $users = User::factory()->count(3)->create();

        $this->artisan('invite:send --all')
            ->assertExitCode(0)
            ->expectsOutput('Sent invitations to 3 user(s).');

        Notification::assertSentTo($users, Invitation::class);
    });

    test('sends invitations to specific users by ID', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $this->artisan('invite:send', ['users' => "{$user1->id},{$user3->id}"])
            ->assertExitCode(0)
            ->expectsOutput('Sent invitations to 2 user(s).');

        Notification::assertSentTo($user1, Invitation::class);
        Notification::assertSentTo($user3, Invitation::class);
        Notification::assertNotSentTo($user2, Invitation::class);
    });

    test('fails when no --all flag and no user IDs provided', function () {
        $this->artisan('invite:send')
            ->assertExitCode(1)
            ->expectsOutput('Please provide either --all flag or comma-separated user IDs');
    });

    test('returns success when no users found', function () {
        $this->artisan('invite:send', ['users' => '999,998,997'])
            ->assertExitCode(0)
            ->expectsOutput('No users found.');
    });

    test('skips users with no email or phone number', function () {
        $userWithEmail = User::factory()->create(['email' => 'test@example.com']);
        $userWithPhone = User::factory()->create(['email' => null, 'phone' => '5551234567']);
        $userWithNeither = User::factory()->create(['email' => null, 'phone' => null]);

        $this->artisan('invite:send --all')
            ->assertExitCode(0)
            ->expectsOutput('Sent invitations to 2 user(s).')
            ->expectsOutput('Skipped 1 user(s) with no email or phone:')
            ->expectsOutput("  - {$userWithNeither->name} ({$userWithNeither->id})");

        Notification::assertSentTo($userWithEmail, Invitation::class);
        Notification::assertSentTo($userWithPhone, Invitation::class);
        Notification::assertNotSentTo($userWithNeither, Invitation::class);
    });
});
