
<?php
use Illuminate\Support\Facades\Route;

Route::prefix('/guest')->group(function () {
    Route::livewire('/', 'pages::users.home')->name('guest.home');
    Route::livewire('/category/{categoryName}', 'pages::users.category-wise-proudcts')->name('guest.category.products');

    Route::livewire('/product/{product}/{productName}', 'pages::users.proudct-details')->name('guest.product.details');

    Route::livewire('/faq', 'pages::users.faq')->name('guest.faq');
});
