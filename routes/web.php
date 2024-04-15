<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'products'], function () {
    Route::get('/', [ProductController::class, 'index']); // Get all products
    Route::post('/store', [ProductController::class, 'store']); // Store a new product
    Route::get('/show', [ProductController::class, 'show']); // Show details of a product
    Route::put('/update', [ProductController::class, 'update']); // Update a product
    Route::delete('/delete', [ProductController::class, 'destroy']); // Delete a product
});
