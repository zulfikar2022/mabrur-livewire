<?php

use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'status', 'admin.only'])->prefix('/admin')->group(function () {
    Route::livewire('/', 'pages::admins.home')->name('admin.home');
    Route::get('/auth/logout', [GoogleController::class, 'logout'])->name('admin.logout');

    Route::livewire('/categories', 'pages::admins.show-all-categories')->name('admin.show-all-categories');
    Route::livewire('/categories/add', 'pages::admins.create-category')->name('admin.create-category');
    // Route::livewire('/categories/{id}', 'pages::admins.see-category-details')->name('admin.see-category-details');
    Route::livewire('/categories/{category}/edit', 'pages::admins.update-category')->name('admin.update-category');
    Route::livewire('/products', 'pages::admins.show-all-products')->name('admin.show-all-products');
    Route::livewire('/products/add', 'pages::admins.add-product')->name('admin.add-product');
    Route::livewire('/products/{id}', 'pages::admins.see-product-details')->name('admin.see-product-details');
    Route::livewire('/products/{product}/edit', 'pages::admins.update-product')->name('admin.update-product');
    Route::livewire('/users', 'pages::admins.see-all-users')->name('admin.see-all-users');
    Route::livewire('/users/disabled', 'pages::admins.see-disabled-users')->name('admin.see-disabled-users');

    Route::livewire('/orders', 'pages::admins.all-orders')->name('admin.all-orders');
    Route::livewire('/orders/pending', 'pages::admins.pending-orders')->name('admin.pending-orders');
    Route::livewire('/orders/approved', 'pages::admins.approved-orders')->name('admin.approved-orders');
    Route::livewire('/orders/shipped', 'pages::admins.shipped-orders')->name('admin.shipped-orders');
    Route::livewire('/orders/delivered', 'pages::admins.delivered-orders')->name('admin.delivered-orders');

    Route::livewire('/orders/cancelled', 'pages::admins.cancelled-orders')->name('admin.cancelled-orders');
    Route::livewire('/orders/delivery-failed', 'pages::admins.delivery-failed-orders')->name('admin.deliver_failed-orders');
    Route::livewire('/orders/returned', 'pages::admins.returned-orders')->name('admin.returned-orders');

    // route for order details
    Route::livewire('/orders/detail/{order}', 'pages::admins.order-details')->name('admin.order-details');
    // route for edit order
    Route::livewire('/orders/update/{order}', 'pages::admins.update-order')->name('admin.update-order');

    Route::livewire('/users/{user}', 'pages::admins.user-details')->name('admin.user-details');

});
