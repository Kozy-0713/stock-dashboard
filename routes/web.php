<?php

use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

// 「/」にアクセスが来たら、StockControllerのindexメソッドを呼ぶ
Route::get('/', [StockController::class, 'index']);
