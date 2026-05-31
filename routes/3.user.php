<?php

use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth','status', 'user.only'])->prefix('/user')->group(function () {
    Route::livewire('/', 'pages::users.home')->name('user.home');
    Route::get('/auth/logout', [GoogleController::class, 'logout'])->name('user.logout');
});
