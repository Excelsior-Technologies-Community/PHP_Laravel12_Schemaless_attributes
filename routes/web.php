<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('products.index');
});

Route::get('/products/export', [ProductController::class, 'export'])
    ->name('products.export');

Route::get('/search-by-attribute', [ProductController::class, 'searchByAttribute'])
    ->name('products.search-by-attribute');

Route::resource('products', ProductController::class);
