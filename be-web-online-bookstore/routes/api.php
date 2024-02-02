<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukusController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AddressesController;
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
    Route::get('/get', [AddressesController::class, 'getAddress']);
    Route::get('/getbyid/{id}', [AddressesController::class, 'getById']);
    Route::put('/update/{id}', [AddressesController::class, 'update']);
    Route::delete('/delete/{id}', [AddressesController::class, 'delete']);
});

Route::prefix('/buku')->middleware('auth:sanctum')->group(function () {
    Route::post('/add', [BukusController::class, 'add']);
    Route::get('/get', [BukusController::class, 'getBuku']);
    Route::get('/getbyid/{id}', [AddressesController::class, 'getById']);
    Route::put('/update/{id}', [AddressesController::class, 'update']);
    Route::delete('/delete/{id}', [BukusController::class, 'delete']);
});