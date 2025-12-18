<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Library\Controllers\Http\ReaderController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
});

Route::get('/library', function () {
    return view('library.index');
});

Route::get('/reviews', function () {
    return view('library.reviews');
});

Route::get('/reader/{bookId}', [ReaderController::class, 'show'])->name('reader.show');
