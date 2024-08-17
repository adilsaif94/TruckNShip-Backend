<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\ShipmentController;


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

Route::get('/test',function () {
    p("Working");
});

Route::post('user/store','App\Http\Controllers\Api\UserController@store');
Route::get('users/get', [UserController::class,'index']);
Route::get('user/{id}', [UserController::class,'show']); 
Route::delete('user/delete/{id}', [UserController::class,'destroy']);
Route::put('update/{id}', [UserController::class,'update']); // to update full data

Route::post('/register', [UserController::class,'register']);
Route::post('/login', [UserController::class,'login']);

// not to open  directly we use middleware

Route::middleware('auth:api')->group(function () {
    Route::get('/user/{id}', [UserController::class,'getUser']);
});

// for shipments

// Public route to create a shipment
Route::post('/shipments', [ShipmentController::class, 'store'])->middleware('auth:api');

// Route to fetch all shipments for the logged-in user
Route::middleware('auth:api')->get('shipments', [ShipmentController::class, 'index']);
Route::middleware('auth:api')->get('/all/shipments', [ShipmentController::class, 'getAllShipments']);
Route::middleware('auth:api')->put('/shipments/{id}/status', [ShipmentController::class, 'updateStatus']);
