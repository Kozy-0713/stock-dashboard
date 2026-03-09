<?php

use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

// 「/」にアクセスが来たら、StockControllerのindexメソッドを呼ぶ
Route::get('/', [StockController::class, 'index']);
// 1. 入力フォームを表示する道
Route::get('/stocks/create', [StockController::class, 'create']);

// 2. フォームから送られたデータを保存する道（POST）
Route::post('/stocks', [StockController::class, 'store']);
// ...
// Route::delete('/stocks/{stock}', [StockController::class, 'destroy'])->name('stocks.destroy');
Route::delete('/stocks/{id}', [StockController::class, 'destroy'])->name('stocks.destroy');

Route::get('/stocks/{id}/edit', [StockController::class, 'edit'])->name('stocks.edit');
Route::put('/stocks/{id}', [StockController::class, 'update'])->name('stocks.update');
Route::post('/stocks/refresh', [StockController::class, 'refreshPrices'])->name('stocks.refresh');
