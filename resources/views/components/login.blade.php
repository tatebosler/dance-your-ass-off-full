<?php

use App\Models\Token;
use App\Models\User;
use App\Notifications\LoginCode;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

new class extends Component
{
    public string $identifier = '';

    public string $code = '';

    public bool $showOtp = false;

    public ?int $userId = null;

    public function sendCode(): void
    {
        $this->validate(['identifier' => 'required|string|max:255']);

        $identifier = trim($this->identifier);
        $user = null;

        if (str_contains($identifier, '@')) {
            if (Validator::make(['email' => $identifier], ['email' => 'email'])->passes()) {
                $user = User::where('email', $identifier)->first();
            }
        } else {
            $digits = preg_replace('/\D/', '', $identifier);
            if (preg_match('/^1?([2-9][01-9][0-9]){2}[0-9]{4}$/', $digits)) {
                $phoneForLookup = strlen($digits) === 11 ? substr($digits, 1) : $digits;
                $user = User::where('phone', $phoneForLookup)->first();
            }
        }

        if ($user !== null) {
            $this->userId = $user->id;
            $tokenValue = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            Token::create(['user_id' => $user->id, 'token' => $tokenValue]);
            $user->notify(new LoginCode($tokenValue));
        }

        $this->showOtp = true;
    }

    public function verify(): void
    {
        $this->validate(['code' => 'required|digits:6']);

        if ($this->userId === null) {
            $this->addError('code', 'Invalid or expired code. Please try again.');

            return;
        }

        $user = User::find($this->userId);

        if ($user === null) {
            $this->addError('code', 'Invalid or expired code. Please try again.');

            return;
        }

        $token = $user->tokens()
            ->where('token', $this->code)
            ->where('created_at', '>=', now()->subHour())
            ->latest()
            ->first();

        if ($token === null) {
            $this->addError('code', 'Invalid or expired code. Please try again.');

            return;
        }

        \Illuminate\Support\Facades\Auth::login($user, remember: true);

        $this->redirect(route('rsvp'), navigate: true);
    }
};
?>

<div class="min-h-screen flex items-center justify-center bg-linear-120 from-purple-500 to-purple-900 px-4">
    <flux:card class="w-full max-w-sm">
        @if (! $showOtp)
            <form wire:submit="sendCode" class="space-y-6">
                <div class="space-y-2 text-center">
                    <flux:heading size="xl">Let's get this party started!</flux:heading>
                    <flux:text>Enter the email address or phone number associated with your invitation.</flux:text>
                </div>

                <flux:input
                    wire:model="identifier"
                    label="Email or phone number"
                    type="text"
                    placeholder="you@example.com or 612-555-0100"
                    autofocus
                />

                <flux:button variant="primary" type="submit" color="yellow" class="w-full cursor-pointer">
                    Retrieve invitation
                </flux:button>
            </form>
        @else
            <form wire:submit="verify" class="space-y-6">
                <div class="space-y-2 text-center">
                    <flux:heading size="xl">Check your messages</flux:heading>
                    <flux:text>We sent a 6-digit code to your email or phone. Enter it below to continue.</flux:text>
                </div>

                <flux:otp
                    wire:model="code"
                    length="6"
                    submit="auto"
                    label="Verification code"
                    label:sr-only
                    :error:icon="false"
                    error:class="text-center"
                    class="mx-auto"
                />

                <flux:button variant="primary" type="submit" class="w-full cursor-pointer" color="yellow">
                    Verify
                </flux:button>

                <flux:button wire:click="$set('showOtp', false)" variant="ghost" class="w-full cursor-pointer">
                    Back
                </flux:button>
            </form>
        @endif
    </flux:card>
</div>


