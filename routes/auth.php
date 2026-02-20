<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Route::get('login', \App\Livewire\Auth\Login::class)->name('login');
    Route::get('register', \App\Livewire\Auth\Register::class)->name('register');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', function () {
        auth()->logout();
        return redirect('/');
    })->name('logout');
});
