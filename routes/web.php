<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::livewire('/login', 'login')->name('login')->middleware(['guest', 'throttle:login']);
Route::livewire('/rsvp', 'rsvp')->name('rsvp')->middleware('auth');
