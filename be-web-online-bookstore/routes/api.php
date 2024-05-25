<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukusController;
use App\Http\Controllers\CartsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AddressesController;
use App\Http\Controllers\CategoriesController;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::put('/user/update', [AuthController::class, 'updateUser']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/getUser', [UsersController::class, 'getAllUsers']);
    Route::delete('/deleteUser/{id}', [UsersController::class, 'deleteUser']);
});

Route::prefix('/address')->middleware('auth:sanctum')->group(function () {
    Route::post('/store', [AddressesController::class, 'store']);
    Route::get('/get', [AddressesController::class, 'getUserAddresses']);
    Route::delete('/{id}', [AddressesController::class, 'delete']);
});

Route::prefix('/buku')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/add', [BukusController::class, 'add']);
        Route::post('/update/{id}', [BukusController::class, 'update']);
        Route::delete('/delete/{id}', [BukusController::class, 'delete']);
    });
    Route::get('/get', [BukusController::class, 'getBuku']);
    Route::get('/detail/{slug}', [BukusController::class, 'getDetailBuku']);
});

Route::prefix('/category')->middleware('auth:sanctum')->group(function () {
    Route::post('/add', [CategoriesController::class, 'store']);
    Route::get('/get', [CategoriesController::class, 'index']);
    Route::post('/update/{id}', [CategoriesController::class, 'update']);
    Route::delete('/delete/{id}', [CategoriesController::class, 'destroy']);
});

Route::prefix('/cart')->middleware('auth:sanctum')->group(function () {
    Route::post('/add', [CartsController::class, 'addToCart']);
    Route::get('/get', [CartsController::class, 'viewCart']);
    Route::put('/update/{id}', [CartsController::class, 'updateCart']);
    Route::delete('/delete/{id}', [CartsController::class, 'removeFromCart']);
    Route::put('/select', [CartsController::class, 'updateSelected']);
});

Route::prefix('/loc')->group(function () {
    Route::get('/areas', [AddressesController::class, 'getAreas']);
});

