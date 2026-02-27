<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PosController;
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('pos.index');
    Route::get('/api/products', [PosController::class, 'getProducts'])->name('pos.products');
    Route::post('/api/products', [PosController::class, 'storeProduct'])->name('pos.storeProduct');
    Route::put('/api/products/{id}', [PosController::class, 'updateProduct'])->name('pos.updateProduct');
    Route::delete('/api/products/{id}', [PosController::class, 'destroyProduct'])->name('pos.destroyProduct');
    Route::get('/api/transactions', [PosController::class, 'getTransactions'])->name('pos.transactions');
    Route::post('/api/transactions', [PosController::class, 'storeTransaction'])->name('pos.storeTransaction');
    Route::delete('/api/transactions/{id}', [PosController::class, 'destroyTransaction'])->name('pos.destroyTransaction');
    Route::get('/receipt/{id}', [PosController::class, 'printReceipt'])->name('pos.printReceipt');
    Route::post('/api/print-wifi', [PosController::class, 'printWiFi'])->name('pos.printWiFi');
    Route::get('/stock-report', [PosController::class, 'stockReport'])->name('pos.stockReport');
});
