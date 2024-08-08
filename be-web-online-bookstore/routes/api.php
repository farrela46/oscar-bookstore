<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukusController;
use App\Http\Controllers\CartsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\BannersController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\AddressesController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\DashboardsController;
use App\Http\Controllers\VerificationsController;

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
    Route::put('/user/admin-update/{id}', [AuthController::class, 'updateUserAdmin']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/email/verify/{id}/{hash}', [VerificationsController::class, 'verify'])->name('verification.verify');
//     Route::post('/email/resend', [VerificationsController::class, 'resend'])->name('verification.resend');
// });

Route::get('/email/verify/{id}/{hash}', [VerificationsController::class, 'verify'])->name('verification.verify');
Route::post('/email/resend', [VerificationsController::class, 'resend'])->name('verification.resend');

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
    Route::get('/checkout', [CartsController::class, 'getCheckout']);
    Route::post('/rates', [CartsController::class, 'getShippingRates']);
});

Route::prefix('/order')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/checkout', [OrdersController::class, 'createOrder']);
        Route::post('/onsite', [OrdersController::class, 'createOfflineOrder']);
        Route::get('/get', [OrdersController::class, 'getUserOrders']);
        Route::get('/getadmin', [OrdersController::class, 'getAdminOrders']);
        Route::get('/status', [OrdersController::class, 'getOrderStatus']);
        Route::post('/create', [OrdersController::class, 'makeOrder']);
        Route::put('/cancel/{id}', [OrdersController::class, 'cancelOrder']);
        Route::post('/update-status', [OrdersController::class, 'UpdateOrderStatus']);
        Route::get('/{transaction_id}', [OrdersController::class, 'getOrderDetail']);
        Route::get('/bs/{bsorderId}', [OrdersController::class, 'retrieveAdminOrder']);
    });
    Route::post('/webhook', [OrdersController::class, 'biteshipWebhook']);
    Route::post('/mdwebhook', [OrdersController::class, 'midtransWebhook']);
});

Route::prefix('/review')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/store', [ReviewsController::class, 'store']);
        Route::get('/all', [ReviewsController::class, 'getAllReviews']);
    });
    Route::get('/{buku_id}', [ReviewsController::class, 'getReviewBook']);
});


Route::prefix('/midtrans')->middleware('auth:sanctum')->group(function () {
    Route::post('/notification', [MidtransController::class, 'handleNotification']);
});

Route::prefix('/loc')->group(function () {
    Route::get('/areas', [AddressesController::class, 'getAreas']);
});


Route::prefix('/dashboard')->middleware('auth:sanctum')->group(function () {
    Route::get('/get', [DashboardsController::class, 'getDashboardData']);
    Route::get('/book', [DashboardsController::class, 'getBookStatistics']);
});


Route::prefix('/banner')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/store', [BannersController::class, 'store']);
        Route::delete('/delete/{id}', [BannersController::class, 'destroy']);
    });
    Route::get('/get', [BannersController::class, 'index']);
});
