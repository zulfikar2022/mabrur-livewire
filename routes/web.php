<?php

use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::livewire('/', 'pages::users.home')->name('home');
Route::livewire('/guest/login', 'pages::users.auth.login')->name('login');



Route::get('/', function () {

    /** @var \App\Models\User $user */
    $user = Auth::user();

    if ($user) {
        if ($user->hasRole('user')) {
            return redirect()->route('user.home');
        } elseif ($user->hasRole('admin')) {
            return redirect()->route('admin.home');
        } elseif ($user->hasRole('super-admin')) {
            return redirect()->route('super-admin.home');
        }
    }
    return redirect()->route('guest.home');
})->name('home');



Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');

Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

// Route::get('/auth/logout', [GoogleController::class, 'logout'])->name('auth.logout')->middleware('auth');

// super-admin, admin, user routes
include __DIR__.'/1.super-admin.php';
include __DIR__.'/2.admin.php';
include __DIR__.'/3.user.php';
include __DIR__.'/4.guest.php';
