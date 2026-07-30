<?php

use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'status', 'super-admin.only'])->prefix('/super-admin')->group(function () {
    Route::get('/auth/logout', [GoogleController::class, 'logout'])->name('super-admin.logout');
    Route::livewire('/', 'pages::super_admins.home')->name('super-admin.home');
    Route::livewire('/admins', 'pages::super_admins.admins')->name('super-admin.admins');
    Route::livewire('/general-users', 'pages::super_admins.general-users')->name('super-admin.general-users');
    Route::livewire('/disabled-users', 'pages::super_admins.disabled-users')->name('super-admin.disabled-users');
});
