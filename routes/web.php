<?php

use App\Http\Controllers\BoardController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect('/login');
});

// Authentication routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// Broadcasting authentication route
Route::middleware(['auth', 'web'])->post('/broadcasting/auth', function () {
    return Illuminate\Support\Facades\Broadcast::auth(request());
})->name('broadcasting.auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [BoardController::class, 'index'])->name('dashboard');
});

