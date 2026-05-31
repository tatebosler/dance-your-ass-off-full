<?php

use App\Models\Party;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public User $user;

    public ?Party $party = null;

    /** @var array<int, string|null> */
    public array $guestRsvp = [];

    /** @var array<int, string> */
    public array $guestPlusOneName = [];

    public function mount(): void
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        $this->user = $authUser;
        $this->party = $authUser->party;

        // Initialize RSVP state for all party members
        if ($this->party) {
            foreach ($this->party->members as $member) {
                $this->guestRsvp[$member->id] = $member->rsvp;
                $this->guestPlusOneName[$member->id] = $member->extra_guest_name ?? '';
            }
        }
    }

    public function updateRsvp(int $guestId, ?string $rsvpValue): void
    {
        $guest = User::find($guestId);
        if (! $guest || $guest->party_id !== $this->party?->id) {
            return;
        }

        // Update the guest's RSVP
        $guest->update(['rsvp' => $rsvpValue]);
        $this->guestRsvp[$guestId] = $rsvpValue;

        // Clear +1 RSVP if main guest doesn't say yes
        if ($rsvpValue !== 'yes') {
            $guest->update([
                'extra_guest_rsvp' => null,
                'extra_guest_name' => null,
            ]);
            $this->guestPlusOneName[$guestId] = '';
        }
    }

    public function updatePlusOneName(int $guestId, string $name): void
    {
        $guest = User::find($guestId);
        if (! $guest || $guest->party_id !== $this->party?->id) {
            return;
        }

        $this->guestPlusOneName[$guestId] = $name;

        // Only save the name if guest said yes and there's a name
        if ($this->guestRsvp[$guestId] === 'yes' && ! empty($name)) {
            $guest->update(['extra_guest_name' => $name]);
        }
    }

    public function updatePlusOneRsvp(int $guestId, ?string $rsvpValue): void
    {
        $guest = User::find($guestId);
        if (! $guest || $guest->party_id !== $this->party?->id) {
            return;
        }

        // Only allow +1 RSVP if main guest said yes and a name is entered
        if ($this->guestRsvp[$guestId] === 'yes' && ! empty($this->guestPlusOneName[$guestId])) {
            $guest->update(['extra_guest_rsvp' => $rsvpValue]);
        }
    }

    public function canPlusOneRespond(int $guestId): bool
    {
        return $this->guestRsvp[$guestId] === 'yes' && ! empty($this->guestPlusOneName[$guestId]);
    }
};
?>

<div class="p-4 sm:p-8">
    <h1 class="text-4xl font-bold mb-2 text-center">You're on the list &mdash; let's get this party started!</h1>

    <h3 class="text-2xl font-bold mb-4 mt-8">Logistics reminder</h3>
    <p><strong>When:</strong> Friday, August 28, 2026 &mdash; doors at 6:30, dinner at 7:00, dancing from 7:30 (ish) until 11 (ish).</p>
    <p><strong>Where:</strong> North Garden Theater, 929 7th St W, Saint Paul, MN 55102</p>
    <p><strong>Dress code:</strong> Dress to impress! There will be awards, and we've heard that at least one person is wearing her wedding dress.</p>

    @if ($party)
        <h3 class="text-2xl font-bold mb-6">Who's coming to the party?</h3>

        <div class="space-y-2 max-w-2xl">
            @forelse ($party->members as $guest)
                <div class="border-2 border-purple-200 rounded-lg bg-white px-3 py-2">
                    {{-- Main Guest Row --}}
                    <div class="flex flex-wrap justify-between items-center gap-2">
                        {{-- Guest Name --}}
                        <div class="font-bold text-base">{{ $guest->name }}</div>

                        {{-- RSVP Buttons --}}
                        <div class="flex gap-2 flex-wrap">
                            <button
                                wire:click="updateRsvp({{ $guest->id }}, 'yes')"
                                @class([
                                    'px-4 py-2 rounded-lg font-bold transition-all text-sm',
                                    'bg-green-500 text-white' => $guestRsvp[$guest->id] === 'yes',
                                    'bg-gray-400 text-white hover:bg-gray-500' => $guestRsvp[$guest->id] !== 'yes',
                                ])
                            >
                                Hell Yes
                            </button>

                            <button
                                wire:click="updateRsvp({{ $guest->id }}, 'maybe')"
                                @class([
                                    'px-4 py-2 rounded-lg font-bold transition-all text-sm',
                                    'bg-yellow-400 text-gray-900' => $guestRsvp[$guest->id] === 'maybe',
                                    'bg-gray-400 text-white hover:bg-gray-500' => $guestRsvp[$guest->id] !== 'maybe',
                                ])
                            >
                                Not sure yet
                            </button>

                            <button
                                wire:click="updateRsvp({{ $guest->id }}, 'no')"
                                @class([
                                    'px-4 py-2 rounded-lg font-bold transition-all text-sm',
                                    'bg-red-600 text-white' => $guestRsvp[$guest->id] === 'no',
                                    'bg-gray-400 text-white hover:bg-gray-500' => $guestRsvp[$guest->id] !== 'no',
                                ])
                            >
                                No :(
                            </button>
                        </div>
                    </div>

                    {{-- +1 Guest Section --}}
                    @if ($guest->extra_guest_allowed)
                        @if ($guestRsvp[$guest->id] === 'yes')
                            {{-- +1 Name Input Row --}}
                            <div class="mt-2 pt-2 border-t border-purple-100">
                                <div class="text-sm italic mb-2">Plus-one of {{ $guest->name }}</div>

                                <input
                                    type="text"
                                    wire:model.debounce.500ms="guestPlusOneName.{{ $guest->id }}"
                                    wire:change="updatePlusOneName({{ $guest->id }}, $event.target.value)"
                                    placeholder="Enter name of additional guest"
                                    class="w-full px-3 py-2 border-2 border-purple-400 bg-white rounded-lg text-sm"
                                />
                            </div>

                            {{-- +1 RSVP Buttons Row --}}
                            @if (! empty($guestPlusOneName[$guest->id]))
                                <div class="flex gap-2 justify-between flex-wrap mt-2">
                                        <button
                                            wire:click="updatePlusOneRsvp({{ $guest->id }}, 'yes')"
                                            @class([
                                                'px-4 py-2 rounded-lg font-bold transition-all text-sm',
                                                'bg-green-500 text-white' => $guest->extra_guest_rsvp === 'yes',
                                                'bg-gray-400 text-white hover:bg-gray-500' => $guest->extra_guest_rsvp !== 'yes',
                                            ])
                                        >
                                            Hell Yes
                                        </button>

                                        <button
                                            wire:click="updatePlusOneRsvp({{ $guest->id }}, 'maybe')"
                                            @class([
                                                'px-4 py-2 rounded-lg font-bold transition-all text-sm',
                                                'bg-yellow-400 text-gray-900' => $guest->extra_guest_rsvp === 'maybe',
                                                'bg-gray-400 text-white hover:bg-gray-500' => $guest->extra_guest_rsvp !== 'maybe',
                                            ])
                                        >
                                            Not sure yet
                                        </button>

                                        <button
                                            wire:click="updatePlusOneRsvp({{ $guest->id }}, 'no')"
                                            @class([
                                                'px-4 py-2 rounded-lg font-bold transition-all text-sm',
                                                'bg-red-600 text-white' => $guest->extra_guest_rsvp === 'no',
                                                'bg-gray-400 text-white hover:bg-gray-500' => $guest->extra_guest_rsvp !== 'no',
                                            ])
                                        >
                                            No :(
                                        </button>
                                </div>
                            @endif
                        @else
                            {{-- Message when guest hasn't said yes --}}
                            <div class="mt-2 pt-2 border-t border-purple-100">
                                <p class="text-sm text-gray-700">{{ $guest->name }} may bring an extra guest. Respond "Hell Yes" to provide their name and response.</p>
                            </div>
                        @endif
                    @endif
                </div>
            @empty
                <p class="text-gray-600">No party members found.</p>
            @endforelse
        </div>

        <p class="mt-8"><strong>Is someone missing from your list?</strong> Please <a href="mailto:tatebosler@gmail.com" class="text-purple-600 hover:text-purple-700 font-black">get in touch with us</a> so we can make any necessary adjustments.</p>
    @else
        <p class="text-gray-600">No party found for your account.</p>
    @endif

</div>
