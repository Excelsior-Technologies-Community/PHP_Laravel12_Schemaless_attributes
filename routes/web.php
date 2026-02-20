<?php
// routes/web.php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('products.index');
});

Route::resource('products', ProductController::class);
Route::get('/search-by-attribute', [ProductController::class, 'searchByAttribute'])
    ->name('products.search-by-attribute');