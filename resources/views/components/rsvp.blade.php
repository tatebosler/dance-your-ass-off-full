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

    public ?string $dietaryRestrictions = null;

    public string $dietaryNotes = '';

    public string $songRequests = '';

    /** @var array<int, string|null> */
    public array $poolPartyRsvp = [];

    /** @var array<int, string|null> */
    public array $stateFairRsvp = [];

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

            // Initialize party-level fields
            $this->dietaryRestrictions = $this->party->dietary_restrictions;
            $this->dietaryNotes = $this->party->dietary_notes ?? '';
            $this->songRequests = $this->party->song_requests ?? '';

            // Initialize per-guest activity RSVPs
            foreach ($this->party->members as $member) {
                $this->poolPartyRsvp[$member->id] = $member->pool_rsvp;
                $this->stateFairRsvp[$member->id] = $member->state_fair_rsvp;
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

    public function updateDietaryRestrictions(string $value): void
    {
        $this->dietaryRestrictions = $value;
        if ($this->party) {
            $this->party->update(['dietary_restrictions' => $value]);
        }
    }

    public function updateDietaryNotes(string $notes): void
    {
        $this->dietaryNotes = $notes;
        if ($this->party) {
            $this->party->update(['dietary_notes' => $notes]);
        }
    }

    public function updateSongRequests(string $requests): void
    {
        $this->songRequests = $requests;
        if ($this->party) {
            $this->party->update(['song_requests' => $requests]);
        }
    }

    public function updatePoolPartyRsvp(int $guestId, ?string $rsvpValue): void
    {
        $guest = User::find($guestId);
        if (! $guest || $guest->party_id !== $this->party?->id) {
            return;
        }

        $guest->update(['pool_rsvp' => $rsvpValue]);
        $this->poolPartyRsvp[$guestId] = $rsvpValue;
    }

    public function updateStateFairRsvp(int $guestId, ?string $rsvpValue): void
    {
        $guest = User::find($guestId);
        if (! $guest || $guest->party_id !== $this->party?->id) {
            return;
        }

        $guest->update(['state_fair_rsvp' => $rsvpValue]);
        $this->stateFairRsvp[$guestId] = $rsvpValue;
    }
};
?>

<div class="p-4 sm:p-8">
    <h1 class="text-4xl font-bold mb-2 text-center">You're on the list &mdash; let's boogie!</h1>
    <p class="text-center">Your responses will save automatically as you make changes &mdash; no need to find a Save button. To make sure everything looks good, just refresh before closing this tab.</p>

    <h3 class="text-2xl font-bold mb-4 mt-8">The details, in a nutshell</h3>
    <div class="space-y-1">
    <p><strong>What:</strong> Dinner, drinks, and dancing to celebrate Wendy's 59&frac12; and Tate's 29&frac12;!</p>
    <p><strong>When:</strong> Friday, August 28, 2026 &mdash; doors at 6:30, dinner at 7:00, dancing from 7:45 (ish) until 11:15 (ish).</p>
    <p><strong>Where:</strong> North Garden Theater, 929 7th St W, Saint Paul, MN 55102</p>
    <p><strong>Dress code:</strong> Dress to impress! (What does that mean? Maybe disco, 70s, 80s, a fancy dress or suit, or whatever you feel most fabulous in!) There will be awards, and we've heard that at least one person is wearing her wedding dress. Also, the dance floor will be open for several hours, and you do NOT want to miss it.</p>
    <p><strong>Food? Drinks?</strong> Yes. Indian food (buffet style) and open bar.</p>
    <p><strong>Gifts?</strong> Nope &mdash; your presence is more than enough!</p>
    <p><strong>More info:</strong> on the <a href="/" class="text-purple-600 hover:text-purple-700 font-black">main page of the website</a></p>
</div>

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

        <p class="mt-4"><strong>Is someone missing from your list?</strong> Please <a href="mailto:tatebosler@gmail.com" class="text-purple-600 hover:text-purple-700 font-black">get in touch with us</a> so we can make any necessary adjustments.</p>

        <p class="mt-4"><strong>Is this not your party?</strong> <a href="/logout" class="text-purple-600 hover:text-purple-700 font-black">Reset your session</a>, then try logging in again. Get in touch with us if you need further assistance.</p>

        @if (collect($guestRsvp)->first(fn($rsvp) => $rsvp === 'yes') !== null)
            <div class="mt-4 space-y-4">
                @unless ($party->local)
                    <h3>Travel logistics</h3>
                    <p><strong>Flights:</strong> book to MSP &mdash; we're a hub for Delta and Sun Country, and we get service from most major carriers. We recommend arriving Friday morning or early afternoon, and leaving Saturday evening or Sunday morning. <em>Doors open for the party at 6:30 on Friday, so please plan accordingly.</em> You absolutely can come in earlier or stay later if you want to make it a longer trip!</p>
                    <p><strong>Hotels:</strong> there aren't any room blocks, so you're free to pick what you want. We generally recommend hotels rather than an Airbnb. Here are some picks:</p>
                    <ul class="list-disc ml-8 space-y-1">
                        <li>Looking for old-school Saint Paul vibes? Check out <a href="https://www.saintpaulhotel.com" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">The Saint Paul Hotel</a>.</li>
                        <li>B&B more your style? <a href="https://newvictorianbb.com" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">The New Victorian B&B</a> is in a great neighborhood with lots of coffee shops and restaurants nearby.</li>
                        <li>Interested in something more unconventional? Check out <a href="https://www.celestestpaul.com" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">Celeste</a> &mdash; an old convent with an eccentric bar.</li>
                        <li>Marriott fans (and anyone looking to stay as close to the venue as possible), we've got you covered: <a href="https://www.marriott.com/en-us/hotels/msprd-residence-inn-st-paul-downtown/overview/" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">the Residence Inn St. Paul Downtown</a> is a great choice for you.</li>
                        <li>If convenience to the airport and Mall of America is important, there are <a href="https://www.choicehotels.com/minnesota/bloomington/radisson-blu-hotels/mn292" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">Radisson Blu</a> and <a href="https://www.marriott.com/en-us/hotels/mspjw-jw-marriott-minneapolis-mall-of-america/overview/" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">JW Marriott</a> options attached to MOA &mdash; a quick Uber or bus ride away from the venue.</li>
                        <li>Finally, if you're a Hyatt loyalist like us, the closest option is <a href="https://www.hyatt.com/hyatt-place/en-US/mspzs-hyatt-place-st-paul-downtown" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">Hyatt Place St. Paul Downtown</a>.</li>
                    </ul>
                    <p><strong>Ground transportation:</strong> You can rent a car (double check which airport terminal you need), but <strong>it's very easy to get around on Metro Transit, Uber, and Lyft.</strong></p>
                @endunless
                <div id="dinner" class="space-y-3">
                    <h3>Dinner</h3>
                    <p>We'll be getting catering from <strong>Indian Kitchen Bar & Grill</strong>, one of our favorite Indian restaurants in Saint Paul. We'll have meat-based and vegetarian options available, and of course lots of rice, naan bread, and other accompaniments.</p>
                    <p>Indian Kitchen's spice level is comparable to other Indian restaurants in the Twin Cities. We'll go mild and medium, and we'll have extra chili flakes available if you want to add more heat.</p>
                    <p>If you're gluten-avoidant: Indian food is generally prepared with gluten-free ingredients. However, Indian Kitchen is a common kitchen and there could be cross contamination.</p>

                    {{-- Dietary Preferences Question --}}
                    <div>
                        <p class="font-semibold mb-3">Does this sound okay to your group?</p>
                        <div class="flex gap-3 flex-wrap">
                            <button
                                wire:click="updateDietaryRestrictions('no')"
                                @class([
                                    'px-4 py-2 rounded-lg font-semibold transition-all text-sm',
                                    'bg-green-500 text-white' => $dietaryRestrictions === 'no',
                                    'bg-gray-300 text-gray-700 hover:bg-gray-400' => $dietaryRestrictions !== 'no',
                                ])
                            >
                                Yes, this sounds great!
                            </button>

                            <button
                                wire:click="updateDietaryRestrictions('yes')"
                                @class([
                                    'px-4 py-2 rounded-lg font-semibold transition-all text-sm',
                                    'bg-yellow-500 text-gray-900' => $dietaryRestrictions === 'yes',
                                    'bg-gray-300 text-gray-700 hover:bg-gray-400' => $dietaryRestrictions !== 'yes',
                                ])
                            >
                                We need to give you a heads up on some dietary needs
                            </button>
                        </div>

                        {{-- Dietary Notes Textarea --}}
                        @if ($dietaryRestrictions === 'yes')
                            <div class="mt-3 max-w-2xl">
                                <label for="dietary-notes" class="block text-sm font-semibold mb-2">Please describe any dietary needs or preferences:</label>
                                <textarea
                                    id="dietary-notes"
                                    wire:model.debounce.1000ms="dietaryNotes"
                                    wire:change="updateDietaryNotes($event.target.value)"
                                    placeholder="Tell us about any dietary restrictions, allergies, or preferences..."
                                    rows="4"
                                    class="w-full px-3 py-2 border-2 border-purple-400 bg-white rounded-lg text-sm"
                                ></textarea>
                            </div>
                        @endif
                    </div>
                </div>

                <div id="songs" class="space-y-3">
                    <h3>Song Requests</h3>
                    <p>If you have song requests, <strong>now</strong> is the time to let us know &mdash; we'll forward them on to our DJ ahead of the party.</p>

                    <label for="song-requests" class="block text-sm font-semibold mb-2">What songs will get you on the dance floor?</label>
                    <textarea
                        id="song-requests"
                        wire:model.debounce.1000ms="songRequests"
                        wire:change="updateSongRequests($event.target.value)"
                        placeholder="List any songs you'd like to hear at the party..."
                        rows="4"
                        class="w-full max-w-2xl px-3 py-2 border-2 border-purple-400 bg-white rounded-lg text-sm"
                    ></textarea>
                </div>
                <div id="activities" class="space-y-4">
                    <h3>Saturday activities</h3>
                    <p>We have a couple of optional activities planned for Saturday. Let us know if you'll be joining!</p>

                    {{-- State Fair --}}
                    <div class="space-y-3">
                        <h4 class="font-bold text-lg">Minnesota State Fair (meet at 10am)</h4>
                        <p>We'll be heading to the State Fair on Saturday morning &mdash; we'll show you a couple of our favorite spots, and then you'll be free to explore on your own. Meet at the Giant Slide at 10:00 AM. (You'll need to purchase your own tickets &mdash; and bring cash or a credit card for food!)</p>

                        <div class="space-y-2 max-w-2xl">
                            @foreach ($party->members as $guest)
                                <div class="flex flex-wrap justify-between items-center gap-2 border border-purple-100 rounded-lg px-3 py-2 bg-white">
                                    <div class="text-sm font-semibold">{{ $guest->name }}</div>
                                    <div class="flex gap-2 flex-wrap">
                                        <button
                                            wire:click="updateStateFairRsvp({{ $guest->id }}, 'yes')"
                                            @class([
                                                'px-4 py-2 rounded-lg font-bold transition-all text-sm',
                                                'bg-green-500 text-white' => $stateFairRsvp[$guest->id] === 'yes',
                                                'bg-gray-400 text-white hover:bg-gray-500' => $stateFairRsvp[$guest->id] !== 'yes',
                                            ])
                                        >
                                            Hell Yes
                                        </button>
                                        <button
                                            wire:click="updateStateFairRsvp({{ $guest->id }}, 'maybe')"
                                            @class([
                                                'px-4 py-2 rounded-lg font-bold transition-all text-sm',
                                                'bg-yellow-400 text-gray-900' => $stateFairRsvp[$guest->id] === 'maybe',
                                                'bg-gray-400 text-white hover:bg-gray-500' => $stateFairRsvp[$guest->id] !== 'maybe',
                                            ])
                                        >
                                            Not sure yet
                                        </button>
                                        <button
                                            wire:click="updateStateFairRsvp({{ $guest->id }}, 'no')"
                                            @class([
                                                'px-4 py-2 rounded-lg font-bold transition-all text-sm',
                                                'bg-red-600 text-white' => $stateFairRsvp[$guest->id] === 'no',
                                                'bg-gray-400 text-white hover:bg-gray-500' => $stateFairRsvp[$guest->id] !== 'no',
                                            ])
                                        >
                                            No :(
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Pool Party --}}
                    <div class="space-y-3">
                        <h4 class="font-bold text-lg">Pool Party (Pool at 3, dinner at 5)</h4>
                        <p>On Saturday afternoon and evening, we'll host a pool party at the Saint Dennis pool (Wendy's house), followed by BBQ on the grill.</p>
                        <p><strong>2023 Upper Saint Dennis Rd, Saint Paul, MN 55116</strong></p>

                        <div class="space-y-2 max-w-2xl">
                            @foreach ($party->members as $guest)
                                <div class="flex flex-wrap justify-between items-center gap-2 border border-purple-100 rounded-lg px-3 py-2 bg-white">
                                    <div class="text-sm font-semibold">{{ $guest->name }}</div>
                                    <div class="flex gap-2 flex-wrap">
                                        <button
                                            wire:click="updatePoolPartyRsvp({{ $guest->id }}, 'yes')"
                                            @class([
                                                'px-4 py-2 rounded-lg font-bold transition-all text-sm',
                                                'bg-green-500 text-white' => $poolPartyRsvp[$guest->id] === 'yes',
                                                'bg-gray-400 text-white hover:bg-gray-500' => $poolPartyRsvp[$guest->id] !== 'yes',
                                            ])
                                        >
                                            Hell Yes
                                        </button>
                                        <button
                                            wire:click="updatePoolPartyRsvp({{ $guest->id }}, 'maybe')"
                                            @class([
                                                'px-4 py-2 rounded-lg font-bold transition-all text-sm',
                                                'bg-yellow-400 text-gray-900' => $poolPartyRsvp[$guest->id] === 'maybe',
                                                'bg-gray-400 text-white hover:bg-gray-500' => $poolPartyRsvp[$guest->id] !== 'maybe',
                                            ])
                                        >
                                            Not sure yet
                                        </button>
                                        <button
                                            wire:click="updatePoolPartyRsvp({{ $guest->id }}, 'no')"
                                            @class([
                                                'px-4 py-2 rounded-lg font-bold transition-all text-sm',
                                                'bg-red-600 text-white' => $poolPartyRsvp[$guest->id] === 'no',
                                                'bg-gray-400 text-white hover:bg-gray-500' => $poolPartyRsvp[$guest->id] !== 'no',
                                            ])
                                        >
                                            No :(
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="text-center space-y-2">
                    <h1>That's all &mdash; we'll see you in August!</h1>
                    <p>If you need to make any changes, just come back to this website!</p>
                </div>
            </div>
        @endif

        @if (collect($guestRsvp)->every(fn($rsvp) => $rsvp === 'no'))
            <h3 class="text-2xl font-bold mb-2 mt-8">Say it ain't so!</h3>
            <p>We'll miss you at the party — thanks for letting us know you won't make it. If your plans change, please come back to this page and update your RSVP.</p>
        @endif
    @else
        <p class="text-gray-600">No party found for your account.</p>
    @endif

</div>
