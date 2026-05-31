<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::livewire('/login', 'login')->name('login')->middleware(['guest', 'throttle:login']);
Route::livewire('/rsvp', 'rsvp')->name('rsvp')->middleware('auth');

Route::match(['get', 'post'], '/logout', function () {
    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout')->middleware('auth');
