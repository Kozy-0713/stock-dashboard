<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StockApiController;

// SPA用のエントリポイント
Route::get('/', function () {
    return view('welcome');
});

// APIプレフィックスをつけてルーティング
Route::prefix('api')->group(function () {
    Route::get('/prices', [StockApiController::class, 'getPrices']);
    Route::get('/exchange', [StockApiController::class, 'getExchangeRate']);
});
