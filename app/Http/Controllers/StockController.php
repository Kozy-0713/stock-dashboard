<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $stocks = Stock::all();

        return view('welcome', compact('stocks'));
    }

    public function create()
    {
        return view('stocks.create');
    }

    public function store(Request $request)
    {
        // 1. 入力チェック（バリデーション）
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric',
            'diff'  => 'required|integer',
        ]);

        // 2. データベースに保存
        Stock::create($validated);

        // 3. 一覧画面に戻る（メッセージ付き）
        return redirect('/')->with('success', '銘柄を登録しました！');
    }

    public function destroy(Stock $stock)
    {
        $stock->delete();
        return redirect('/')->with('success', '銘柄を削除しました');
    }
}