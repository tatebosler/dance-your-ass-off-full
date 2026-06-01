<?php

use App\Models\Token;
use App\Models\User;
use App\Notifications\LoginCode;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
});

describe('Login Page', function () {
    test('guests can access the login page', function () {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSeeLivewire('login');
    });

    test('authenticated users are redirected from login to rsvp', function () {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/login');
        $response->assertRedirectToRoute('rsvp');
    });
});

describe('sendCode Action', function () {
    test('accepts a valid email address', function () {
        $user = User::factory()->create(['email' => 'test@example.com', 'phone' => null]);

        Livewire::test('login')
            ->set('identifier', 'test@example.com')
            ->call('sendCode')
            ->assertSet('showOtp', true)
            ->assertSet('userId', $user->id);

        expect(Token::where('user_id', $user->id)->exists())->toBeTrue();
    });

    test('accepts a valid phone number without leading 1', function () {
        $user = User::factory()->create(['email' => null, 'phone' => '6125550100']);

        Livewire::test('login')
            ->set('identifier', '612-555-0100')
            ->call('sendCode')
            ->assertSet('showOtp', true)
            ->assertSet('userId', $user->id);

        expect(Token::where('user_id', $user->id)->exists())->toBeTrue();
    });

    test('accepts a valid phone number with leading 1', function () {
        $user = User::factory()->create(['email' => null, 'phone' => '6125550100']);

        Livewire::test('login')
            ->set('identifier', '1-612-555-0100')
            ->call('sendCode')
            ->assertSet('showOtp', true)
            ->assertSet('userId', $user->id);

        expect(Token::where('user_id', $user->id)->exists())->toBeTrue();
    });

    test('shows OTP screen even if user not found', function () {
        Livewire::test('login')
            ->set('identifier', 'nonexistent@example.com')
            ->call('sendCode')
            ->assertSet('showOtp', true)
            ->assertSet('userId', null);

        expect(Token::exists())->toBeFalse();
    });

    test('generates a 6-digit token with leading zeros', function () {
        $user = User::factory()->create(['email' => 'test@example.com']);

        Livewire::test('login')
            ->set('identifier', 'test@example.com')
            ->call('sendCode');

        $token = Token::where('user_id', $user->id)->first();
        expect($token->token)->toMatch('/^\d{6}$/');
    });

    test('sends notification to user with email', function () {
        $user = User::factory()->create(['email' => 'test@example.com']);

        Livewire::test('login')
            ->set('identifier', 'test@example.com')
            ->call('sendCode');

        Notification::assertSentTo($user, LoginCode::class);
    });

    test('sets two reply-to addresses on the login email', function () {
        $user = User::factory()->create(['email' => 'test@example.com']);

        Livewire::test('login')
            ->set('identifier', 'test@example.com')
            ->call('sendCode');

        Notification::assertSentTo($user, LoginCode::class, function (LoginCode $notification) use ($user) {
            $mailMessage = $notification->toMail($user);

            return $mailMessage->replyTo === [
                ['tatebosler@gmail.com', 'Tate Bosler'],
                ['wendylutter@gmail.com', 'Wendy Lutter'],
            ];
        });
    });

    test('sends SMS notification if user has phone but no email', function () {
        $user = User::factory()->create(['email' => null, 'phone' => '6125550100']);

        Livewire::test('login')
            ->set('identifier', '612-555-0100')
            ->call('sendCode');

        Notification::assertSentTo($user, LoginCode::class);
    });

    test('rejects invalid email address', function () {
        Livewire::test('login')
            ->set('identifier', 'not-an-email')
            ->call('sendCode')
            ->assertSet('showOtp', true)
            ->assertSet('userId', null);
    });

    test('rejects invalid phone number', function () {
        Livewire::test('login')
            ->set('identifier', '555-0100')
            ->call('sendCode')
            ->assertSet('showOtp', true)
            ->assertSet('userId', null);
    });

    test('rejects empty identifier', function () {
        Livewire::test('login')
            ->set('identifier', '')
            ->call('sendCode')
            ->assertHasErrors('identifier');
    });
});

describe('verify Action', function () {
    test('logs in user with valid token', function () {
        $user = User::factory()->create();
        $token = Token::create(['user_id' => $user->id, 'token' => '123456']);

        Livewire::test('login')
            ->set('userId', $user->id)
            ->set('code', '123456')
            ->call('verify')
            ->assertRedirectToRoute('rsvp');

        $this->assertAuthenticatedAs($user);
    });

    test('sets remember token when logging in', function () {
        $user = User::factory()->create();
        Token::create(['user_id' => $user->id, 'token' => '123456']);

        Livewire::test('login')
            ->set('userId', $user->id)
            ->set('code', '123456')
            ->call('verify');

        $user->refresh();
        expect($user->remember_token)->not()->toBeNull();
    });

    test('rejects expired token', function () {
        $user = User::factory()->create();

        Livewire::test('login')
            ->set('userId', $user->id)
            ->set('code', '123456')
            ->call('verify');

        $this->assertGuest();
    });

    test('rejects incorrect code', function () {
        $user = User::factory()->create();
        Token::create(['user_id' => $user->id, 'token' => '123456']);

        Livewire::test('login')
            ->set('userId', $user->id)
            ->set('code', '654321')
            ->call('verify');

        $this->assertGuest();
    });

    test('rejects code if user not found', function () {
        Livewire::test('login')
            ->set('userId', 9999)
            ->set('code', '123456')
            ->call('verify')
            ->assertHasErrors('code');

        $this->assertGuest();
    });

    test('requires 6 digit code', function () {
        Livewire::test('login')
            ->set('userId', 1)
            ->set('code', '12345')
            ->call('verify')
            ->assertHasErrors('code');
    });

    test('accepts token created within one hour', function () {
        $user = User::factory()->create();
        Token::create([
            'user_id' => $user->id,
            'token' => '123456',
            'created_at' => now()->subMinutes(59),
        ]);

        Livewire::test('login')
            ->set('userId', $user->id)
            ->set('code', '123456')
            ->call('verify')
            ->assertRedirectToRoute('rsvp');

        $this->assertAuthenticatedAs($user);
    });
});

describe('Rate Limiting', function () {
    test('allows 5 requests per minute per IP', function () {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->get('/login');
            expect($response->status())->toBe(200);
        }
    });

    test('rejects 6th request within the same minute', function () {
        for ($i = 0; $i < 5; $i++) {
            $this->get('/login');
        }

        $response = $this->get('/login');
        expect($response->status())->toBe(429);
    });
});
