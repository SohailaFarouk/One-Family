<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\SubscriptionController;

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


Route::post('register',[UserController::class, 'register']);
Route::post('login',[UserController::class, 'login']);
Route::post('logout',[UserController::class, 'logout']);

Route::group(['prefix' => 'products'], function () {
    Route::get('/', [ProductController::class, 'index']); // Get all products
    Route::post('/store', [ProductController::class, 'store']); // Store a new product
    Route::get('/show', [ProductController::class, 'show']); // Show details of a product
    Route::put('/update', [ProductController::class, 'update']); // Update a product
    Route::delete('/delete', [ProductController::class, 'destroy']); // Delete a product
    Route::post('/shop', [ProductController::class, 'shop']); //parent can reserve a product

});
Route::group(['prefix' => 'events'], function () {
    Route::get('/', [EventController::class, 'index']); // Get all events
    Route::post('/store', [EventController::class, 'store']); // Store a new event
    Route::get('/show', [EventController::class, 'show']); // Show details of an event
    Route::put('/update', [EventController::class, 'update']); // Update an event
    Route::delete('/delete', [EventController::class, 'destroy']); // Delete an event
    Route::post('/reserve', [EventController::class, 'reserve']); // parent Reserve an event

});
Route::group(['prefix' => 'vouchers'], function () {
    Route::get('/', [VoucherController::class, 'index']); // Get all vouchers
    Route::post('/store', [VoucherController::class, 'store']); // Store a new voucher
    Route::get('/show', [VoucherController::class, 'show']); // Show details of an voucher
    Route::put('/update', [VoucherController::class, 'update']); // Update an voucher
    Route::delete('/delete', [VoucherController::class, 'destroy']); // Delete an voucher
});

Route::group(['prefix' => 'appointments'], function () {
    Route::get('/', [AppointmentController::class, 'index']); // Get all appointments
    Route::post('/store', [AppointmentController::class, 'store']); // store a new appointments
});
Route::group(['prefix' => 'sessions'], function () {
    Route::get('/', [SessionController::class, 'index']); // Get all session
    Route::post('/store', [SessionController::class, 'store']); // store a new session
    Route::post('/reserve', [SessionController::class, 'reserve']); //
});
Route::post('/subscriptionplans',[SubscriptionController::class, 'subscriptionCard']);

Route::post('/subscribe',[SubscriptionController::class, 'subscribe']);