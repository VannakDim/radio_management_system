<?php

use App\Http\Controllers\BorrowController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportToPdf;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductModelController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\SetFrequencyController;
use Illuminate\Support\Facades\Route;



Route::get('/', [HomeController::class, 'home'])->name('home');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class,'dashboard'])->name('dashboard');

    Route::get('/product/all', [ProductController::class, 'index'])->name('all.product');
    Route::get('/product/create', [ProductController::class, 'create'])->name('create.product');
    Route::get('/product/model/show', [ProductModelController::class, 'index'])->name('model.show');
    Route::get('/product/model/edit/{id}', [ProductModelController::class, 'edit'])->name('model.edit');
    Route::post('/product/model/update/{id}', [ProductModelController::class, 'update'])->name('model.update');
    Route::get('/product/softDel/{id}', [ProductModelController::class, 'softDelete']);
    Route::get('/product/model', [ProductModelController::class, 'create'])->name('product.model.create');
    Route::post('/product/store', [ProductModelController::class, 'store'])->name('store.model');
    Route::post('/product/check-serial-number', [ProductController::class, 'checkSerialNumber'])->name('check.serial.number');

    Route::get('/product/stock-in/index', [StockInController::class, 'index'])->name('stockin.index');
    Route::get('/product/stock-in', [StockInController::class, 'create'])->name('stockin.create');
    Route::post('/product/stock-in/store', [StockInController::class, 'store'])->name('stockin.store');
    Route::get('/product/stock-in/edit/{id}', [StockInController::class, 'edit'])->name('stockin.edit');
    Route::put('/product/stock-in/update/{id}', [StockInController::class, 'update'])->name('stockin.update');
    
    // StockIn with Product
    Route::get('/product/stock-in/{id}', [StockInController::class, 'create_product'])->name('stockin.product');
    Route::post('/product/stock-in/store-product', [StockInController::class, 'store_product'])->name('stockin.store.product');

    Route::get('/product/stock-out/index', [StockOutController::class, 'index'])->name('stockout.index');
    Route::get('/product/stock-out', [StockOutController::class, 'create'])->name('stockout.create');
    Route::post('/product/stock-out/store', [StockOutController::class, 'store'])->name('stockout.store');
    Route::get('/product/stock-out/edit/{id}', [StockOutController::class, 'edit'])->name('stockout.edit');
    Route::put('/product/stock-out/update/{id}', [StockOutController::class, 'update'])->name('stockout.update');

    Route::get('/product/borrow', [BorrowController::class, 'create'])->name('borrow.create');
    Route::post('/product/borrow/store', [BorrowController::class, 'store'])->name('borrow.store');
    Route::get('/product/borrow/index', [BorrowController::class, 'index'])->name('borrow.index');
    Route::get('/product/borrow/edit/{id}', [BorrowController::class, 'edit'])->name('borrow.edit');
    Route::put('/product/borrow/update/{id}', [BorrowController::class, 'update'])->name('borrow.update');
    Route::get('/product/borrow/retrun/{id}', [BorrowController::class, 'return_index'])->name('return.index');
    Route::put('/product/borrow/return/{id}', [BorrowController::class, 'return'])->name('borrow.return');

    //Set Frequency
    Route::get('/set-frequency', [SetFrequencyController::class, 'create'])->name('frequency.create');
    Route::post('/set-frequency/store', [SetFrequencyController::class, 'store'])->name('frequency.store');
    
    //Print feature
    Route::get('/product/stock-in/show/{id}', [ExportToPdf::class, 'previewStockIn'])->name('stockin.preview');
    Route::get('/product/stock-out/show/{id}', [ExportToPdf::class, 'exportStockOutPdf'])->name('stockout.show');
    Route::get('/product/borrow/show/{id}', [ExportToPdf::class, 'exportBorrow'])->name('borrow.show');
    Route::get('/product/return/show/{id}', [ExportToPdf::class, 'printReturn'])->name('return.print');


    // Paginattion data
    Route::get('/product/stock-out/data', [StockOutController::class, 'paginateData'])->name('stockout.paginate');
    Route::get('/product/borrow/data', [BorrowController::class, 'paginateData'])->name('borrow.paginate');
    
    // Download feature
    Route::get('/product/stock-in/download/{id}', [StockInController::class, 'download'])->name('stockin.download');
    Route::get('/product/borrow/download/{id}', [BorrowController::class, 'download'])->name('borrow.download');
    Route::get('/product/stock-out/download/{id}', [StockOutController::class, 'download'])->name('stockout.download');

    Route::get('/logout', [UserAuthController::class, 'logout'])->name('user.logout');

});
