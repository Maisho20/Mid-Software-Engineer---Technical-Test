<?php

use App\Http\Controllers\Api\IncomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserPocketController;

Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('auth/profile', [AuthController::class, 'profile']);
    Route::post('pockets', [UserPocketController::class, 'store']);
    Route::get('pockets', [UserPocketController::class, 'list']);
    Route::post('incomes', [IncomeController::class, 'store']);
});
