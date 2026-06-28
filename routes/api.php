<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\UnitApiController;
use App\Http\Controllers\Api\StockApiController;
use App\Http\Controllers\Api\BorrowApiController;
use App\Http\Controllers\Api\FrequencyApiController;
use Illuminate\Support\Facades\Route;

// Public Auth routes
Route::post('/login', [AuthController::class, 'login']);

// Authenticated API routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard Statistics
    Route::get('/dashboard', [DashboardApiController::class, 'getSummary']);

    // Products & Models
    Route::get('/product-models', [ProductApiController::class, 'getModels']);
    Route::post('/products/check-serial', [ProductApiController::class, 'checkSerialNumber']);
    Route::get('/search/global', [\App\Http\Controllers\Api\SearchApiController::class, 'apiSearch']);

    // Units
    Route::get('/units', [UnitApiController::class, 'index']);

    // Stock In / Out
    Route::get('/stock-ins', [StockApiController::class, 'getStockIns']);
    Route::post('/stock-ins', [StockApiController::class, 'storeStockIn']);
    Route::post('/stock-ins/assign', [StockApiController::class, 'assignStockInProducts']);
    Route::get('/stock-outs', [StockApiController::class, 'getStockOuts']);
    Route::post('/stock-outs', [StockApiController::class, 'storeStockOut']);

    // Borrow & Return
    Route::get('/borrows', [BorrowApiController::class, 'getBorrows']);
    Route::post('/borrows', [BorrowApiController::class, 'storeBorrow']);
    Route::post('/borrows/{id}/return', [BorrowApiController::class, 'returnBorrow']);

    // Frequency Configs
    Route::get('/set-frequencies', [FrequencyApiController::class, 'index']);
    Route::post('/set-frequencies', [FrequencyApiController::class, 'store']);
    Route::post('/set-frequencies/{id}/update-image', [FrequencyApiController::class, 'updateImage']);
});

