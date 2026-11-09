<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
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
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

    // kasir & owner
    Route::get('/pos', [TransactionController::class, 'pos'])->name('pos');
    Route::post('/pos', [TransactionController::class, 'store'])->name('pos.store');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/transactions/{id}/struk', [TransactionController::class, 'struk'])->name('transactions.struk');

    // simulasi pembayaran QRIS
    Route::get('/transactions/{id}/qris', [TransactionController::class, 'qris'])->name('qris.show');
    Route::post('/transactions/{id}/qris/bayar', [TransactionController::class, 'qrisPay'])->name('qris.pay');
    Route::post('/transactions/{id}/qris/batal', [TransactionController::class, 'qrisCancel'])->name('qris.cancel');

    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/history', [StockController::class, 'history'])->name('stock.history');

    // notifikasi stok menipis
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts/read-all', [AlertController::class, 'readAll'])->name('alerts.read-all');
    Route::post('/alerts/{id}/read', [AlertController::class, 'read'])->name('alerts.read');

    // khusus owner
    Route::middleware('owner')->group(function () {
        Route::resource('branches', BranchController::class)->except(['show']);
        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('users', UserController::class)->except(['show']);

        Route::get('/stock/create', [StockController::class, 'create'])->name('stock.create');
        Route::post('/stock', [StockController::class, 'store'])->name('stock.store');
        Route::get('/stock/{id}/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
        Route::post('/stock/{id}/adjust', [StockController::class, 'adjustUpdate'])->name('stock.adjust.update');

        // transfer stok antar cabang / gudang
        Route::get('/stock-transfer', [StockTransferController::class, 'index'])->name('stock-transfer.index');
        Route::get('/stock-transfer/create', [StockTransferController::class, 'create'])->name('stock-transfer.create');
        Route::post('/stock-transfer', [StockTransferController::class, 'store'])->name('stock-transfer.store');

        // laporan keuangan + ekspor
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
        Route::get('/reports/excel', [ReportController::class, 'excel'])->name('reports.excel');
    });
});
