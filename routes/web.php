<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComingSoonController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

// login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// semua halaman di bawah ini harus login dulu
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // kasir & owner
    Route::get('/pos', [TransactionController::class, 'pos'])->name('pos');
    Route::post('/pos', [TransactionController::class, 'store'])->name('pos.store');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');

    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');

    // khusus owner
    Route::middleware('owner')->group(function () {
        Route::resource('branches', BranchController::class)->except(['show']);
        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('/stock/create', [StockController::class, 'create'])->name('stock.create');
        Route::post('/stock', [StockController::class, 'store'])->name('stock.store');

        // belum dikerjakan
        Route::get('/stock-transfer', [ComingSoonController::class, 'stockTransfer'])->name('stock-transfer.index');
        Route::get('/reports', [ComingSoonController::class, 'reports'])->name('reports.index');
    });
});
