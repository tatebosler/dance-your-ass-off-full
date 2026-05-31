<?php

use App\Models\Party;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public User $user;

    public ?Party $party = null;

    public function mount(): void
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        $this->user = $authUser;
        $this->party = $authUser->party;
    }
};
?>

<div class="max-w-2xl mx-auto px-4 py-8">
    <flux:heading size="xl">Welcome, {{ $user->name }}</flux:heading>

    {{-- RSVP form goes here --}}
</div>

