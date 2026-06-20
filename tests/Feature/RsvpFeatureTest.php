<?php

use App\Models\Party;
use App\Models\User;
use Livewire\Livewire;

describe('RSVP Page', function () {
    test('guests are redirected to login', function () {
        $response = $this->get('/rsvp');
        $response->assertRedirectToRoute('login');
    });

    test('guest with valid invitation id is logged in and can access rsvp page', function () {
        $user = User::factory()->create([
            'magic_link_token' => str_repeat('a', 64),
        ]);

        $response = $this->get('/rsvp?invitationId='.str_repeat('a', 64));

        $response->assertStatus(200);
        $response->assertSeeLivewire('rsvp');
        $this->assertAuthenticatedAs($user);
    });

    test('guest with invalid invitation id is redirected to login', function () {
        User::factory()->create([
            'magic_link_token' => str_repeat('a', 64),
        ]);

        $response = $this->get('/rsvp?invitationId=invalid-token');

        $response->assertRedirectToRoute('login');
        $this->assertGuest();
    });

    test('authenticated users can access rsvp page', function () {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/rsvp');
        $response->assertStatus(200);
        $response->assertSeeLivewire('rsvp');
    });

    test('hydrates user to livewire component', function () {
        $user = User::factory()->create(['name' => 'Jane Smith']);
        Livewire::actingAs($user)->test('rsvp')
            ->assertSet('user.name', 'Jane Smith')
            ->assertSet('user.id', $user->id);
    });

    test('hydrates party to livewire component', function () {
        $party = Party::factory()->create(['name' => 'Main Party']);
        $user = User::factory()->create(['party_id' => $party->id]);
        Livewire::actingAs($user)->test('rsvp')
            ->assertSet('party.name', 'Main Party')
            ->assertSet('party.id', $party->id);
    });

    test('handles null party', function () {
        $user = User::factory()->create(['party_id' => null]);
        Livewire::actingAs($user)->test('rsvp')
            ->assertSet('user.id', $user->id)
            ->assertSet('party', null);
    });

    test('user can update their rsvp status', function () {
        $party = Party::factory()->create();
        $user = User::factory()->create(['party_id' => $party->id, 'rsvp' => null]);

        Livewire::actingAs($user)->test('rsvp')
            ->call('updateRsvp', $user->id, 'yes')
            ->assertSet('guestRsvp.'.$user->id, 'yes');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'rsvp' => 'yes',
        ]);
    });

    test('user can change rsvp status', function () {
        $party = Party::factory()->create();
        $user = User::factory()->create(['party_id' => $party->id, 'rsvp' => 'yes']);

        Livewire::actingAs($user)->test('rsvp')
            ->call('updateRsvp', $user->id, 'no')
            ->assertSet('guestRsvp.'.$user->id, 'no');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'rsvp' => 'no',
        ]);
    });

    test('changing rsvp to no clears plus one data', function () {
        $party = Party::factory()->create();
        $user = User::factory()->create([
            'party_id' => $party->id,
            'rsvp' => 'yes',
            'extra_guest_allowed' => true,
            'extra_guest_name' => 'Jane Doe',
            'extra_guest_rsvp' => 'yes',
        ]);

        Livewire::actingAs($user)->test('rsvp')
            ->call('updateRsvp', $user->id, 'no');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'rsvp' => 'no',
            'extra_guest_name' => null,
            'extra_guest_rsvp' => null,
        ]);
    });

    test('plus one name can only be saved when guest says yes', function () {
        $party = Party::factory()->create();
        $user = User::factory()->create([
            'party_id' => $party->id,
            'rsvp' => 'maybe',
            'extra_guest_allowed' => true,
        ]);

        Livewire::actingAs($user)->test('rsvp')
            ->call('updatePlusOneName', $user->id, 'John Doe');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'extra_guest_name' => null,
        ]);
    });

    test('plus one name is saved when guest says yes', function () {
        $party = Party::factory()->create();
        $user = User::factory()->create([
            'party_id' => $party->id,
            'rsvp' => 'yes',
            'extra_guest_allowed' => true,
        ]);

        Livewire::actingAs($user)->test('rsvp')
            ->call('updatePlusOneName', $user->id, 'John Doe');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'extra_guest_name' => 'John Doe',
        ]);
    });

    test('plus one rsvp is only allowed when name is entered and guest says yes', function () {
        $party = Party::factory()->create();
        $user = User::factory()->create([
            'party_id' => $party->id,
            'rsvp' => 'yes',
            'extra_guest_allowed' => true,
        ]);

        Livewire::actingAs($user)->test('rsvp')
            ->call('updatePlusOneRsvp', $user->id, 'yes');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'extra_guest_rsvp' => null,
        ]);
    });

    test('plus one rsvp is saved when name is entered and guest says yes', function () {
        $party = Party::factory()->create();
        $user = User::factory()->create([
            'party_id' => $party->id,
            'rsvp' => 'yes',
            'extra_guest_allowed' => true,
            'extra_guest_name' => 'John Doe',
        ]);

        Livewire::actingAs($user)->test('rsvp')
            ->call('updatePlusOneRsvp', $user->id, 'yes');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'extra_guest_rsvp' => 'yes',
        ]);
    });

    test('each party member has independent rsvp state', function () {
        $party = Party::factory()->create();
        $user1 = User::factory()->create(['party_id' => $party->id, 'rsvp' => null]);
        $user2 = User::factory()->create(['party_id' => $party->id, 'rsvp' => null]);

        Livewire::actingAs($user1)->test('rsvp')
            ->call('updateRsvp', $user1->id, 'yes')
            ->assertSet('guestRsvp.'.$user1->id, 'yes')
            ->assertSet('guestRsvp.'.$user2->id, null);

        $this->assertDatabaseHas('users', [
            'id' => $user1->id,
            'rsvp' => 'yes',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user2->id,
            'rsvp' => null,
        ]);
    });

    test('user can update pool party rsvp', function () {
        $party = Party::factory()->create();
        $user = User::factory()->create(['party_id' => $party->id, 'pool_rsvp' => null]);

        Livewire::actingAs($user)->test('rsvp')
            ->call('updatePoolPartyRsvp', $user->id, 'yes')
            ->assertSet('poolPartyRsvp.'.$user->id, 'yes');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'pool_rsvp' => 'yes',
        ]);
    });

    test('each party member has independent pool party rsvp', function () {
        $party = Party::factory()->create();
        $user1 = User::factory()->create(['party_id' => $party->id, 'pool_rsvp' => null]);
        $user2 = User::factory()->create(['party_id' => $party->id, 'pool_rsvp' => null]);

        Livewire::actingAs($user1)->test('rsvp')
            ->call('updatePoolPartyRsvp', $user1->id, 'yes')
            ->assertSet('poolPartyRsvp.'.$user1->id, 'yes')
            ->assertSet('poolPartyRsvp.'.$user2->id, null);
    });

    test('user can update state fair rsvp', function () {
        $party = Party::factory()->create();
        $user = User::factory()->create(['party_id' => $party->id, 'state_fair_rsvp' => null]);

        Livewire::actingAs($user)->test('rsvp')
            ->call('updateStateFairRsvp', $user->id, 'maybe')
            ->assertSet('stateFairRsvp.'.$user->id, 'maybe');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'state_fair_rsvp' => 'maybe',
        ]);
    });

    test('each party member has independent state fair rsvp', function () {
        $party = Party::factory()->create();
        $user1 = User::factory()->create(['party_id' => $party->id, 'state_fair_rsvp' => null]);
        $user2 = User::factory()->create(['party_id' => $party->id, 'state_fair_rsvp' => null]);

        Livewire::actingAs($user1)->test('rsvp')
            ->call('updateStateFairRsvp', $user1->id, 'no')
            ->assertSet('stateFairRsvp.'.$user1->id, 'no')
            ->assertSet('stateFairRsvp.'.$user2->id, null);
    });

    test('activity rsvp cannot be set by a user from another party', function () {
        $party1 = Party::factory()->create();
        $party2 = Party::factory()->create();
        $user1 = User::factory()->create(['party_id' => $party1->id]);
        $user2 = User::factory()->create(['party_id' => $party2->id, 'pool_rsvp' => null]);

        Livewire::actingAs($user1)->test('rsvp')
            ->call('updatePoolPartyRsvp', $user2->id, 'yes');

        $this->assertDatabaseHas('users', [
            'id' => $user2->id,
            'pool_rsvp' => null,
        ]);
    });

    test('activities section is visible when party is invited to extras', function () {
        $party = Party::factory()->create(['invited_to_extras' => true]);
        $user = User::factory()->create(['party_id' => $party->id, 'rsvp' => 'yes']);

        Livewire::actingAs($user)->test('rsvp')
            ->assertSee('Saturday activities');
    });

    test('activities section is hidden when party is not invited to extras', function () {
        $party = Party::factory()->create(['invited_to_extras' => false]);
        $user = User::factory()->create(['party_id' => $party->id, 'rsvp' => 'yes']);

        Livewire::actingAs($user)->test('rsvp')
            ->assertDontSee('Saturday activities')
            ->assertDontSee('Minnesota State Fair')
            ->assertDontSee('Pool Party');
    });
});
