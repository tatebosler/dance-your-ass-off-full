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

<div class="p-4 sm:p-8">
    <h1 class="text-4xl font-bold mb-2">You're on the list &mdash; let's get this party started!</h1>

    <p>This invitation covers {{ $party->members->count() }} {{ Str::plural('guest', $party->members->count()) }}.</p>
    <ul class="list-disc list-inside my-2 ml-8">
        @foreach ($party->members as $user)
            <li>
                {{ $user->name }}
                @if ($user->id === $this->user->id)
                    <em class="font-light">(You)</em>
                @endif
                @if ($user->extra_guest_allowed)
                    <em class="font-light">({{ $user->extra_guest_name ? 'plus ' . $user->extra_guest_name : 'extra guest allowed' }})</em>
                @endif
            </li>
        @endforeach
    </ul>
    <p><strong>Is someone missing from your list?</strong> Please <a href="mailto:tatebosler@gmail.com" class="text-purple-600 hover:text-purple-700 font-black">get in touch with us</a> so we can make any necessary adjustments.</p>

</div>
