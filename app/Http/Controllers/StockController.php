<?php

namespace App\Http\Controllers;

use App\Models\Stock; // 1. 追加
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        // 2. 配列を消して、一行でDBから全データを取得！
        $stocks = Stock::all();

        return view('welcome', compact('stocks'));
    }

    public function create()
    {
        // 登録用の画面を表示するだけ
        return view('stocks.create');
    }
}