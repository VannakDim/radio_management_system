<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportToPdf;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductModelController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserAuthController;
use App\Models\Borrow;
use App\Models\BorrowDetail;
use App\Models\ProductModel;
use App\Models\StockInDetail;
use App\Models\StockOut;
use App\Models\StockOutDetail;
use App\Models\StockOutProduct;
use App\Models\User;
use Illuminate\Http\Request;
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
    Route::get('/product/stock-in/show/{id}', [ExportToPdf::class, 'previewStockIn'])->name('stockin.preview');
    Route::get('/product/stock-in/download/{id}', [StockInController::class, 'download'])->name('stockin.download');
    Route::get('/product/stock-in/edit/{id}', [StockInController::class, 'edit'])->name('stockin.edit');
    Route::put('/product/stock-in/update/{id}', [StockInController::class, 'update'])->name('stockin.update');

    Route::get('/product/stock-out', [StockOutController::class, 'create'])->name('stockout.create');
    Route::post('/product/stock-out/store', [StockOutController::class, 'store'])->name('stockout.store');
    Route::get('/product/stock-out/show/{id}', [ExportToPdf::class, 'exportStockOutPdf'])->name('stockout.show');

    Route::get('/product/borrow', [BorrowController::class, 'create'])->name('borrow.create');
    Route::get('/product/borrow/show/{id}', [ExportToPdf::class, 'exportBorrow'])->name('borrow.show');
    Route::post('/product/borrow/store', [BorrowController::class, 'store'])->name('borrow.store');
    Route::get('/product/borrow/index', [BorrowController::class, 'index'])->name('borrow.index');

    Route::get('/product/stock-out/data', [StockOutController::class, 'paginateData'])->name('stockout.paginate');
    Route::get('/product/borrow/data', [BorrowController::class, 'paginateData'])->name('borrow.paginate');

    Route::get('/product/borrow/download/{id}', [BorrowController::class, 'download'])->name('borrow.download');
    Route::get('/product/stock-out/download/{id}', [StockOutController::class, 'download'])->name('stockout.download');

    Route::get('/logout', [UserAuthController::class, 'logout'])->name('user.logout');

});
