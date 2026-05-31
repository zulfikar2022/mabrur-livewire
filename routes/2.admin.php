<?php

use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth','status', 'admin.only'])->prefix('/admin')->group(function () {
    Route::livewire('/', 'pages::admins.home')->name('admin.home');
    Route::get('/auth/logout', [GoogleController::class, 'logout'])->name('admin.logout');

    Route::livewire('/categories', 'pages::admins.show-all-categories')->name('admin.show-all-categories');
    Route::livewire('/products', 'pages::admins.show-all-products')->name('admin.show-all-products');
    Route::livewire('/products/add', 'pages::admins.add-product')->name('admin.add-product');
    Route::livewire('/products/{id}', 'pages::admins.see-product-details')->name('admin.see-product-details');
    Route::livewire('/products/{product}/edit', 'pages::admins.update-product')->name('admin.update-product');
    Route::livewire('/users', 'pages::admins.see-all-users')->name('admin.see-all-users');
    Route::livewire('/users/disabled', 'pages::admins.see-disabled-users')->name('admin.see-disabled-users');
});
