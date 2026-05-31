
<?php
use Illuminate\Support\Facades\Route;

Route::prefix('/guest')->group(function () {
    Route::livewire('/', 'pages::users.home')->name('guest.home');
    Route::livewire('/{categoryName}', 'pages::users.category-wise-proudcts')->name('guest.category.products');
});
