<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/profile', function() {
        return auth()->user();
    });
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Routes accessible to users with role 1 is admin
    Route::group(['middleware' => ['role:1']], function () {
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/invoice', [InvoiceController::class, 'index']);
        Route::post('/invoice', [InvoiceController::class, 'store']);
        Route::get('/invoice/{code}', [InvoiceController::class, 'show']);
    });
});