<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\AuthenticationController;
use App\Http\Controllers\api\v1\UserDetailsController;
use App\Http\Controllers\api\v1\OutletController;
use App\Http\Controllers\api\v1\MenusController;
use App\Http\Controllers\api\v1\TransactionController;
use App\Http\Controllers\api\v1\TablesController;

Route::get('/v1', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Welcome to Meetopo API!'
    ]);
});

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

    // === USERS - Cashier ===
    Route::middleware('userAccess:Pemilik')->group(function () {
        Route::get('/cashiers', [AuthenticationController::class, 'getCashiers']);
        Route::post('auth/user/create-cashier', [AuthenticationController::class, 'createCashier']);
        Route::delete('/cashier/{id}', [AuthenticationController::class, 'deleteCashier']);
    });

    // === OUTLETS ===
    Route::middleware('userAccess:Pemilik')->group(function () {
        Route::apiResource('outlets', OutletController::class);
        Route::post('/outlets/assign-cashier/{outlet_id}', [OutletController::class, 'assignCashier']);
    });

    Route::middleware('userAccess:Pemilik|Kasir')->group(function () {
        Route::get('/outlets', [OutletController::class, 'index']);
    });

    // === MENUS ===
    Route::get('/menus/{outlet_id}', [MenusController::class, 'getMenusByOutletId']);

    Route::middleware('userAccess:Pemilik')->group(function () {
        Route::post('/menus/bulk-insert', [MenusController::class, 'bulkInsert']);
        Route::delete('/menus/{id}', [MenusController::class, 'destroy']);
    });

    // === TRANSACTIONS ===
    Route::middleware('userAccess:Pemilik')->group(function () {
        Route::get('/transactions/{outlet_id}', [TransactionController::class, 'getTransactionsByOutletId']);
    });
    
    Route::middleware('userAccess:Pemilik|Kasir')->group(function () {
        Route::post('/transactions/bulk-insert', [TransactionController::class, 'bulkInsert']);
    });

    // === TABLES ===
    Route::middleware('userAccess:Pemilik')->group(function () {
        Route::post('/tables/bulk-insert', [TablesController::class, 'bulkInsert']);
        Route::delete('/tables/{id}', [TablesController::class, 'destroy']);
    });

    Route::middleware('userAccess:Pemilik|Kasir')->group(function () {
        Route::get('/tables/{outlet_id}', [TablesController::class, 'getTablesByOutletId']);
        Route::post('/tables/lock/{id}', [TablesController::class, 'lockTable']);
        Route::post('/tables/unlock/{id}', [TablesController::class, 'unlockTable']);
    });
});