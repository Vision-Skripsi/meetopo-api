<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\AuthenticationController;
use App\Http\Controllers\api\v1\UserDetailsController;
use App\Http\Controllers\api\v1\OutletController;
use App\Http\Controllers\api\v1\MenusController;
use App\Http\Controllers\api\v1\TransactionController;
use App\Http\Controllers\api\v1\TablesController;

Route::prefix('v1')->group(function(){
    Route::get('auth/missing-token', [AuthenticationController::class, 'missingToken'])->name('login');
    Route::post('auth/user/registration', [AuthenticationController::class, 'registration']);
    Route::post('auth/user/login', [AuthenticationController::class, 'login']);
    Route::get('/outlets-public', [OutletController::class, 'index']);
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('auth/user', [AuthenticationController::class, 'getUserData']);
    Route::get('user/details/{user_id}', [UserDetailsController::class, 'show']);
    Route::put('user/details/{user_id}', [UserDetailsController::class, 'update']);

    // === OUTLETS ===
    Route::middleware('userAccess:Owner')->group(function () {
        Route::apiResource('outlets', OutletController::class);
    });

    Route::middleware('userAccess:Owner, Cashier')->group(function () {
        Route::get('/outlets', [OutletController::class, 'index']);
    });

    // === MENUS ===
    Route::get('/menus/{outlet_id}', [MenusController::class, 'getMenusByOutletId']);

    Route::middleware('userAccess:Owner')->group(function () {
        Route::post('/menus/bulk-insert', [MenusController::class, 'bulkInsert']);
        Route::delete('/menus/{id}', [MenusController::class, 'destroy']);
    });

    // === TRANSACTIONS ===
    Route::middleware('userAccess:Owner')->group(function () {
        Route::get('/transactions/{outlet_id}', [TransactionController::class, 'getTransactionsByOutletId']);
    });
    
    Route::middleware('userAccess:Owner,Cashier')->group(function () {
        Route::post('/transactions/bulk-insert', [TransactionController::class, 'bulkInsert']);
    });

    // === TABLES ===
    Route::middleware('userAccess:Owner')->group(function () {
        Route::post('/tables/bulk-insert', [TablesController::class, 'bulkInsert']);
        Route::delete('/tables/{id}', [TablesController::class, 'destroy']);
    });

    Route::middleware('userAccess:Owner,Cashier')->group(function () {
        Route::get('/tables/{outlet_id}', [TablesController::class, 'getTablesByOutletId']);
        Route::post('/tables/lock/{id}', [TablesController::class, 'lockTable']);
        Route::post('/tables/unlock/{id}', [TablesController::class, 'unlockTable']);
    });
});