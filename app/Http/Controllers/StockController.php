<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        // 1. 本来はDBから取りますが、まずは手書きのデータ（配列）を用意
        $stocks = [
            ['name' => 'トヨタ', 'price' => 53500.5, 'diff' => 30050],
            ['name' => 'ソニーG', 'price' => 512500.0, 'diff' => -25000],
            ['name' => 'ソフトバンクG', 'price' => 58200.0, 'diff' => -30000],
        ];

        // 2. view（welcome.blade.php）にデータを渡して表示！
        return view('welcome', compact('stocks'));
    }
}