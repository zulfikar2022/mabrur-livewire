<?php

use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth','status', 'user.only'])->prefix('/user')->group(function () {
    // Route::livewire('/', 'pages::users.home')->name('user.home');
    Route::get('/auth/logout', [GoogleController::class, 'logout'])->name('user.logout');

    Route::livewire('/cart/{user}', 'pages::users.user-cart-page')->name('user.cart');

    // Route::livewire('/category/{categoryName}', 'pages::users.category-wise-proudcts')->name('user.category.products');
    // Route::livewire('/product/{product}/{productName}', 'pages::users.proudct-details')->name('user.product.details');

    Route::livewire('/my-orders', 'pages::users.my-orders')->name('user.my.orders');

    Route::livewire('/my-orders/{order}', 'pages::users.order-details')->name('user.order.details');
    Route::livewire('/profile', 'pages::users.user-profile')->name('user.profile');

    // Route::livewire('/faq', 'pages::users.faq')->name('user.faq');



});
