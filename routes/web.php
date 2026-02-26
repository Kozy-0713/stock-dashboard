<?php

use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

// 「/」にアクセスが来たら、StockControllerのindexメソッドを呼ぶ
Route::get('/', [StockController::class, 'index']);
// 1. 入力フォームを表示する道
Route::get('/stocks/create', [StockController::class, 'create']);

// 2. フォームから送られたデータを保存する道（POST）
Route::post('/stocks', [StockController::class, 'store']);