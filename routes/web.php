<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ShowLoginController;
use App\Http\Controllers\Auth\SignInAsController;
use App\Http\Controllers\LogoutController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/tickets')->name('home');

Route::get('/login', ShowLoginController::class)->name('login');
Route::post('/login', LoginController::class)->name('login.attempt');

if (app()->environment(['local', 'testing'])) {
    Route::post('/login/as', SignInAsController::class)->name('login.as');
}

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', LogoutController::class)->name('logout');
});
