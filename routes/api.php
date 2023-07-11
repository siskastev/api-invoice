<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ProductController;

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

    Route::get('/profile', function () {
        return auth()->user();
    });

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    Route::post('/logout', [AuthController::class, 'logout']);

    // Routes accessible to users with role 1 is admin
    Route::group(['middleware' => ['role:1']], function () {

        //customers
        Route::prefix('customers')->group(function () {
            Route::controller(CustomerController::class)->group(function () {
                Route::post('/', 'store');
                Route::put('/{id}', 'update');
                Route::get('/', 'index');
                Route::get('/{id}', 'show');
                Route::delete('/{id}', 'destroy');
            });
        });

        //products
        Route::prefix('products')->group(function () {
            Route::controller(ProductController::class)->group(function () {
                Route::post('/', 'store');
                Route::put('/{id}', 'update');
                Route::delete('/{id}', 'destroy');
            });
        });

        //invoice
        Route::prefix('invoice')->group(function () {
            Route::controller(InvoiceController::class)->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::put('/{code}', 'update');
                Route::get('/{code}', 'show');
            });
        });
    });
});
