
<?php
use Illuminate\Support\Facades\Route;

Route::prefix('/guest')->group(function () {
    Route::livewire('/', 'pages::users.home')->name('guest.home');
});
