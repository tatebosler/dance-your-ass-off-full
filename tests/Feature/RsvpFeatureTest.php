<?php

use App\Models\Party;
use App\Models\User;
use Livewire\Livewire;

describe('RSVP Page', function () {
    test('guests are redirected to login', function () {
        $response = $this->get('/rsvp');
        $response->assertRedirectToRoute('login');
    });

    test('authenticated users can access rsvp page', function () {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/rsvp');
        $response->assertStatus(200);
        $response->assertSeeLivewire('rsvp');
    });

    test('displays user name at the top', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        $response = $this->actingAs($user)->get('/rsvp');
        $response->assertSee('Welcome, John Doe');
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
});
