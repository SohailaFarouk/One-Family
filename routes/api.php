<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register',[UserController::class, 'register']);
Route::post('login',[UserController::class, 'login']);
Route::group(['prefix' => 'products'], function () {
    Route::get('/', [ProductController::class, 'index']); // Get all products
    Route::post('/store', [ProductController::class, 'store']); // Store a new product
    Route::get('/show', [ProductController::class, 'show']); // Show details of a product
    Route::put('/update', [ProductController::class, 'update']); // Update a product
    Route::delete('/delete', [ProductController::class, 'destroy']); // Delete a product
});