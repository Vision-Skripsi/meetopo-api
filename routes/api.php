<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\AuthenticationController;
use App\Http\Controllers\api\v1\UserDetailsController;
use App\Http\Controllers\api\v1\OutletController;
use App\Http\Controllers\api\v1\MenusController;


Route::prefix('v1')->group(function(){
    Route::get('auth/missing-token', [AuthenticationController::class, 'missingToken'])->name('login');
    Route::post('auth/user/registration', [AuthenticationController::class, 'registration']);
    Route::post('auth/user/login', [AuthenticationController::class, 'login']);
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('user/details/{user_id}', [UserDetailsController::class, 'show']);
    Route::put('user/details/{user_id}', [UserDetailsController::class, 'update']);
});

Route::prefix('v1')->group(function(){
    Route::apiResource('outlets-public', OutletController::class);
    Route::get('/menus/{outlet_id}', [MenusController::class, 'getMenusByOutletId']);
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function(){
    Route::middleware('userAccess:cashier')->group(function () {
        Route::apiResource('outlets', OutletController::class);    
        Route::post('/menus/bulk-insert', [MenusController::class, 'bulkInsert']);
        Route::delete('/menus/{menu}', [MenusController::class, 'destroy']);
    });

    Route::get('auth/user', [AuthenticationController::class, 'getUserData']);
});