<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TagsController;
use App\Http\Controllers\Api\VilleController;
use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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



// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/auth/google', [AuthController::class, 'RegisterWithGoogle']);
Route::post('/login/google', [AuthController::class, 'LoginWithGoogle']);

Route::post('auth/login', [AuthController::class, 'login']);
Route::get('/user', [AuthController::class, 'getUserById']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/villes', [VilleController::class, 'getAllVilles']);
Route::put('/user/update/{id}', [OrderController::class, 'updateUser']);

Route::get('/products', [ProductController::class, 'index']);
Route::post('/add-product', [ProductController::class, 'store']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);
Route::get('/products-details/{id}', [ProductController::class, 'getProductById']);

//Tags
Route::get('/tags', [TagsController::class, 'index']);
//Categories
Route::get('/categories', [CategoriesController::class, 'index']);


Route::middleware('auth:api')->group(function () {
});

Route::post('/add-to-cart', [CartController::class, 'addToCart']);
Route::get('/cart', [CartController::class, 'getCart']);
Route::delete('/cart', [CartController::class, 'removeFromCart']);
Route::put('/cart/update-quantity', [CartController::class, 'updateCartItemQuantity']);
Route::get('/cart/item-count', [CartController::class, 'getCartItemCount']);
Route::delete('/cart/clear', [CartController::class, 'clearCart']);
Route::post('/send-order', [OrderController::class, 'sendOrder']);
Route::get('/admin/orders', [OrderController::class, 'getAllOrders']);
Route::get('/admin/order-counts', [OrderController::class, 'getOrderCounts']);
